from datetime import datetime
from decimal import Decimal
from typing import Annotated
from uuid import uuid4
import os
import shutil

from fastapi import Depends, FastAPI, Header, HTTPException, Query, UploadFile, File
from fastapi.middleware.cors import CORSMiddleware
from fastapi.staticfiles import StaticFiles
from sqlalchemy import select
from sqlalchemy.orm import Session

from .database import Base, SessionLocal, engine
from .models import (
    Account,
    Cart,
    CartItem,
    EscrowStatus,
    ListingStatus,
    MutationDirection,
    MutationRefType,
    MutationType,
    Order,
    OrderItem,
    OrderItemStatus,
    OrderItemStatusLog,
    Product,
    RoleType,
    Wallet,
    WalletMutation,
)
from .schemas import (
    AccountOut,
    AdminAdjustBalanceIn,
    AdminCreateAccountIn,
    AdminUpdateAccountIn,
    AdminUpdateProductStatusIn,
    CartItemIn,
    CartItemOut,
    CheckoutOut,
    ComplaintIn,
    LoginIn,
    OrderItemOut,
    ProductCreateIn,
    ProductOut,
    ProductUpdateIn,
    RegisterIn,
    StatusUpdateIn,
    TokenOut,
    WalletMutationOut,
    WalletOut,
)
from .security import create_access_token, decode_access_token, hash_password, verify_password

app = FastAPI(title="Ecommerce Simpel API", version="1.0.0")

os.makedirs("uploads", exist_ok=True)
app.mount("/uploads", StaticFiles(directory="uploads"), name="uploads")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:5173", "http://127.0.0.1:5173"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.on_event("startup")
def on_startup() -> None:
    Base.metadata.create_all(bind=engine)
    db = SessionLocal()
    try:
        # Seed admin default biar bisa langsung jalan.
        admin = db.scalar(select(Account).where(Account.role == RoleType.admin))
        if not admin:
            admin = Account(
                role=RoleType.admin,
                full_name="System Admin",
                username="admin",
                email="admin@local.test",
                password_hash=hash_password("admin123"),
                is_active=True,
            )
            db.add(admin)
            db.flush()
            db.add(
                Wallet(
                    account_id=admin.id,
                    wallet_number=f"WAL-{admin.id:06d}",
                    balance=Decimal("0.00"),
                )
            )
            db.commit()
    finally:
        db.close()


def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


def ensure_wallet(db: Session, account_id: int) -> Wallet:
    wallet = db.scalar(select(Wallet).where(Wallet.account_id == account_id))
    if wallet:
        return wallet
    wallet = Wallet(
        account_id=account_id,
        wallet_number=f"WAL-{account_id:06d}",
        balance=Decimal("0.00"),
    )
    db.add(wallet)
    db.flush()
    return wallet


def to_decimal(value: float | Decimal) -> Decimal:
    return Decimal(str(value)).quantize(Decimal("0.01"))


def sync_product_status(product: Product) -> None:
    product.status = (
        ListingStatus.stok_kosong
        if product.stock <= 0
        else ListingStatus.stok_tersedia
    )


def get_current_account(
    authorization: Annotated[str | None, Header()] = None,
    db: Session = Depends(get_db),
) -> Account:
    if not authorization or not authorization.startswith("Bearer "):
        raise HTTPException(status_code=401, detail="Token tidak valid")

    token = authorization.replace("Bearer ", "", 1).strip()
    payload = decode_access_token(token)
    if not payload:
        raise HTTPException(status_code=401, detail="Token tidak valid")

    account_id = payload.get("sub")
    if not account_id:
        raise HTTPException(status_code=401, detail="Token tidak valid")

    account = db.get(Account, int(account_id))
    if not account or not account.is_active:
        raise HTTPException(status_code=401, detail="Akun tidak aktif")
    return account


def require_role(account: Account, roles: set[RoleType]) -> None:
    if account.role not in roles:
        raise HTTPException(status_code=403, detail="Role tidak diizinkan")


def log_status_change(
    db: Session,
    item: OrderItem,
    old_status: OrderItemStatus,
    new_status: OrderItemStatus,
    actor_id: int,
    note: str | None = None,
) -> None:
    db.add(
        OrderItemStatusLog(
            order_item_id=item.id,
            old_status=old_status,
            new_status=new_status,
            changed_by_account_id=actor_id,
            note=note,
        )
    )


def settle_order_escrow(db: Session, order_id: int) -> None:
    order = db.get(Order, order_id)
    if not order:
        return

    items = db.scalars(select(OrderItem).where(OrderItem.order_id == order_id)).all()
    if not items:
        return

    final_statuses = {
        OrderItemStatus.diterima_pembeli,
        OrderItemStatus.transaksi_gagal,
    }
    if not all(item.status in final_statuses for item in items):
        return

    all_failed = all(item.status == OrderItemStatus.transaksi_gagal for item in items)
    if all_failed:
        order.escrow_status = EscrowStatus.refunded
        order.refunded_at = datetime.utcnow()
    else:
        order.escrow_status = EscrowStatus.released
        order.released_at = datetime.utcnow()


@app.get("/")
def health_check():
    return {"message": "API ecommerce berjalan"}


@app.post("/auth/register", response_model=AccountOut)
def register(payload: RegisterIn, db: Session = Depends(get_db)):
    exists = db.scalar(
        select(Account).where(
            (Account.username == payload.username) | (Account.email == payload.email)
        )
    )
    if exists:
        raise HTTPException(status_code=400, detail="Username/email sudah dipakai")

    account = Account(
        role=RoleType.user,
        full_name=payload.full_name,
        username=payload.username,
        email=payload.email,
        password_hash=hash_password(payload.password),
        is_active=True,
    )
    db.add(account)
    db.flush()
    ensure_wallet(db, account.id)
    db.commit()
    db.refresh(account)
    return account


@app.post("/auth/login", response_model=TokenOut)
def login(payload: LoginIn, db: Session = Depends(get_db)):
    account = db.scalar(select(Account).where(Account.username == payload.username))
    if not account or not verify_password(payload.password, account.password_hash):
        raise HTTPException(status_code=401, detail="Login gagal")

    token = create_access_token({"sub": str(account.id), "role": account.role.value})
    return TokenOut(access_token=token)


@app.get("/me", response_model=AccountOut)
def me(current: Account = Depends(get_current_account)):
    return current


@app.get("/me/wallet", response_model=WalletOut)
def my_wallet(current: Account = Depends(get_current_account), db: Session = Depends(get_db)):
    wallet = ensure_wallet(db, current.id)
    db.commit()
    return wallet


@app.get("/products", response_model=list[ProductOut])
def list_products(db: Session = Depends(get_db)):
    return db.scalars(select(Product).order_by(Product.id.desc())).all()


@app.post("/products/upload-image")
def upload_product_image(
    file: UploadFile = File(...),
    current: Account = Depends(get_current_account),
):
    require_role(current, {RoleType.user})
    
    ext = file.filename.split(".")[-1] if "." in file.filename else "jpg"
    filename = f"{uuid4().hex}.{ext}"
    filepath = os.path.join("uploads", filename)
    
    with open(filepath, "wb") as buffer:
        shutil.copyfileobj(file.file, buffer)
        
    return {"image_url": f"http://localhost:8000/uploads/{filename}"}


@app.post("/products", response_model=ProductOut)
def create_product(
    payload: ProductCreateIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.user})

    product = Product(
        seller_id=current.id,
        name=payload.name,
        description=payload.description,
        image_url=payload.image_url,
        price=to_decimal(payload.price),
        stock=payload.stock,
    )
    sync_product_status(product)
    db.add(product)
    db.commit()
    db.refresh(product)
    return product


@app.patch("/products/{product_id}", response_model=ProductOut)
def update_product(
    product_id: int,
    payload: ProductUpdateIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.user})
    product = db.get(Product, product_id)
    if not product:
        raise HTTPException(status_code=404, detail="Produk tidak ditemukan")
    if product.seller_id != current.id:
        raise HTTPException(status_code=403, detail="Bukan pemilik produk")

    for field, value in payload.model_dump(exclude_none=True).items():
        setattr(product, field, value)
    sync_product_status(product)

    db.commit()
    db.refresh(product)
    return product


@app.delete("/products/{product_id}")
def delete_own_product(
    product_id: int,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.user})
    product = db.get(Product, product_id)
    if not product:
        raise HTTPException(status_code=404, detail="Produk tidak ditemukan")
    if product.seller_id != current.id:
        raise HTTPException(status_code=403, detail="Bukan pemilik produk")

    db.delete(product)
    db.commit()
    return {"message": "Produk dihapus"}


def get_or_create_cart(db: Session, buyer_id: int) -> Cart:
    cart = db.scalar(select(Cart).where(Cart.buyer_id == buyer_id))
    if cart:
        return cart
    cart = Cart(buyer_id=buyer_id)
    db.add(cart)
    db.flush()
    return cart


@app.get("/cart", response_model=list[CartItemOut])
def view_cart(
    current: Account = Depends(get_current_account), db: Session = Depends(get_db)
):
    require_role(current, {RoleType.user})
    cart = get_or_create_cart(db, current.id)
    db.commit()

    rows = db.scalars(select(CartItem).where(CartItem.cart_id == cart.id)).all()
    result: list[CartItemOut] = []
    for row in rows:
        product = db.get(Product, row.product_id)
        if not product:
            continue
        result.append(
            CartItemOut(
                id=row.id,
                product_id=row.product_id,
                quantity=row.quantity,
                product_name=product.name,
                unit_price=float(product.price),
                subtotal=float(to_decimal(product.price) * row.quantity),
            )
        )
    return result


@app.post("/cart/items")
def add_cart_item(
    payload: CartItemIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.user})
    cart = get_or_create_cart(db, current.id)

    product = db.get(Product, payload.product_id)
    if not product:
        raise HTTPException(status_code=404, detail="Produk tidak ditemukan")

    if payload.quantity > product.stock:
        raise HTTPException(status_code=400, detail="Jumlah melebihi stok")

    item = db.scalar(
        select(CartItem).where(
            CartItem.cart_id == cart.id, CartItem.product_id == payload.product_id
        )
    )
    if item:
        new_qty = item.quantity + payload.quantity
        if new_qty > product.stock:
            raise HTTPException(status_code=400, detail="Jumlah melebihi stok")
        item.quantity = new_qty
    else:
        item = CartItem(cart_id=cart.id, product_id=payload.product_id, quantity=payload.quantity)
        db.add(item)

    db.commit()
    return {"message": "Item ditambahkan"}


@app.patch("/cart/items/{item_id}")
def update_cart_item(
    item_id: int,
    payload: CartItemIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.user})
    cart = get_or_create_cart(db, current.id)

    item = db.get(CartItem, item_id)
    if not item or item.cart_id != cart.id:
        raise HTTPException(status_code=404, detail="Item keranjang tidak ditemukan")

    if item.product_id != payload.product_id:
        raise HTTPException(status_code=400, detail="product_id tidak boleh diubah")

    product = db.get(Product, payload.product_id)
    if not product or payload.quantity > product.stock:
        raise HTTPException(status_code=400, detail="Jumlah melebihi stok")

    item.quantity = payload.quantity
    db.commit()
    return {"message": "Item keranjang diupdate"}


@app.delete("/cart/items/{item_id}")
def remove_cart_item(
    item_id: int,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.user})
    cart = get_or_create_cart(db, current.id)
    item = db.get(CartItem, item_id)
    if not item or item.cart_id != cart.id:
        raise HTTPException(status_code=404, detail="Item keranjang tidak ditemukan")
    db.delete(item)
    db.commit()
    return {"message": "Item keranjang dihapus"}


@app.post("/checkout", response_model=CheckoutOut)
def checkout(
    current: Account = Depends(get_current_account), db: Session = Depends(get_db)
):
    require_role(current, {RoleType.user})

    cart = get_or_create_cart(db, current.id)
    cart_items = db.scalars(select(CartItem).where(CartItem.cart_id == cart.id)).all()
    if not cart_items:
        raise HTTPException(status_code=400, detail="Keranjang kosong")

    buyer_wallet = ensure_wallet(db, current.id)
    total = Decimal("0.00")
    product_map: dict[int, Product] = {}

    for c in cart_items:
        product = db.get(Product, c.product_id)
        if not product:
            raise HTTPException(status_code=400, detail="Ada produk tidak valid")
        if c.quantity > product.stock:
            raise HTTPException(status_code=400, detail=f"Stok produk {product.name} tidak cukup")
        product_map[c.product_id] = product
        total += to_decimal(product.price) * c.quantity

    if to_decimal(buyer_wallet.balance) < total:
        raise HTTPException(status_code=400, detail="Saldo tidak cukup")

    order = Order(
        order_number=f"ORD-{uuid4().hex[:10].upper()}",
        buyer_id=current.id,
        total_amount=total,
        escrow_status=EscrowStatus.funded,
        paid_at=datetime.utcnow(),
    )
    db.add(order)
    db.flush()

    buyer_wallet.balance = to_decimal(buyer_wallet.balance) - total
    db.add(
        WalletMutation(
            wallet_id=buyer_wallet.id,
            direction=MutationDirection.debit,
            amount=total,
            type=MutationType.checkout_debit,
            ref_type=MutationRefType.order,
            ref_id=order.id,
            description="Checkout pesanan",
        )
    )

    for c in cart_items:
        product = product_map[c.product_id]
        subtotal = to_decimal(product.price) * c.quantity

        product.stock -= c.quantity
        sync_product_status(product)

        item = OrderItem(
            order_id=order.id,
            product_id=product.id,
            seller_id=product.seller_id,
            buyer_id=current.id,
            product_name_snapshot=product.name,
            product_description_snapshot=product.description,
            product_image_snapshot=product.image_url,
            unit_price_snapshot=to_decimal(product.price),
            quantity=c.quantity,
            subtotal=subtotal,
            status=OrderItemStatus.menunggu_penjual,
        )
        db.add(item)

        db.delete(c)

    db.commit()
    return CheckoutOut(order_id=order.id, order_number=order.order_number, total_amount=float(total))


@app.get("/orders/items/mine", response_model=list[OrderItemOut])
def my_order_items(
    as_seller: bool = Query(default=False),
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.user})
    if as_seller:
        items = db.scalars(select(OrderItem).where(OrderItem.seller_id == current.id)).all()
    else:
        items = db.scalars(select(OrderItem).where(OrderItem.buyer_id == current.id)).all()

    return [
        OrderItemOut(
            id=i.id,
            order_id=i.order_id,
            seller_id=i.seller_id,
            buyer_id=i.buyer_id,
            courier_id=i.courier_id,
            product_name_snapshot=i.product_name_snapshot,
            quantity=i.quantity,
            subtotal=float(i.subtotal),
            status=i.status,
            complaint_reason=i.complaint_reason,
        )
        for i in items
    ]


@app.patch("/seller/order-items/{item_id}/status")
def seller_update_status(
    item_id: int,
    payload: StatusUpdateIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.user})
    item = db.get(OrderItem, item_id)
    if not item or item.seller_id != current.id:
        raise HTTPException(status_code=404, detail="Item pesanan tidak ditemukan")

    allowed = {
        OrderItemStatus.menunggu_penjual: {OrderItemStatus.diproses_penjual},
        OrderItemStatus.diproses_penjual: {OrderItemStatus.menunggu_kurir},
    }

    if payload.new_status not in allowed.get(item.status, set()):
        raise HTTPException(status_code=400, detail="Transisi status tidak valid")

    old = item.status
    item.status = payload.new_status
    log_status_change(db, item, old, payload.new_status, current.id, payload.note)
    db.commit()
    return {"message": "Status item diperbarui"}


@app.get("/courier/order-items", response_model=list[OrderItemOut])
def courier_list_items(
    current: Account = Depends(get_current_account), db: Session = Depends(get_db)
):
    require_role(current, {RoleType.courier})
    visible = {
        OrderItemStatus.menunggu_kurir,
        OrderItemStatus.sedang_dikirim,
        OrderItemStatus.dikirim_balik,
    }
    items = db.scalars(select(OrderItem).where(OrderItem.status.in_(visible))).all()

    return [
        OrderItemOut(
            id=i.id,
            order_id=i.order_id,
            seller_id=i.seller_id,
            buyer_id=i.buyer_id,
            courier_id=i.courier_id,
            product_name_snapshot=i.product_name_snapshot,
            quantity=i.quantity,
            subtotal=float(i.subtotal),
            status=i.status,
            complaint_reason=i.complaint_reason,
        )
        for i in items
    ]


@app.patch("/courier/order-items/{item_id}/status")
def courier_update_status(
    item_id: int,
    payload: StatusUpdateIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.courier})

    item = db.get(OrderItem, item_id)
    if not item:
        raise HTTPException(status_code=404, detail="Item pesanan tidak ditemukan")

    allowed = {
        OrderItemStatus.menunggu_kurir: {
            OrderItemStatus.sedang_dikirim,
            OrderItemStatus.dikirim_balik,
        },
        OrderItemStatus.sedang_dikirim: {OrderItemStatus.sampai_di_tujuan},
        OrderItemStatus.dikirim_balik: {OrderItemStatus.menunggu_penjual},
        OrderItemStatus.dikomplain: {OrderItemStatus.dikirim_balik},
    }

    if payload.new_status not in allowed.get(item.status, set()):
        raise HTTPException(status_code=400, detail="Transisi status tidak valid")

    if item.status == OrderItemStatus.menunggu_kurir and item.courier_id is None:
        item.courier_id = current.id

    old = item.status
    item.status = payload.new_status
    log_status_change(db, item, old, payload.new_status, current.id, payload.note)
    db.commit()
    return {"message": "Status item diperbarui"}


@app.patch("/buyer/order-items/{item_id}/received")
def buyer_mark_received(
    item_id: int,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.user})
    item = db.get(OrderItem, item_id)
    if not item or item.buyer_id != current.id:
        raise HTTPException(status_code=404, detail="Item pesanan tidak ditemukan")
    if item.status != OrderItemStatus.sampai_di_tujuan:
        raise HTTPException(status_code=400, detail="Status saat ini tidak bisa diterima")

    old = item.status
    item.status = OrderItemStatus.diterima_pembeli
    log_status_change(db, item, old, item.status, current.id, "Diterima pembeli")

    seller_wallet = ensure_wallet(db, item.seller_id)
    seller_wallet.balance = to_decimal(seller_wallet.balance) + to_decimal(item.subtotal)
    db.add(
        WalletMutation(
            wallet_id=seller_wallet.id,
            direction=MutationDirection.credit,
            amount=to_decimal(item.subtotal),
            type=MutationType.seller_payout,
            ref_type=MutationRefType.order_item,
            ref_id=item.id,
            description="Pencairan dana ke penjual",
        )
    )

    settle_order_escrow(db, item.order_id)
    db.commit()
    return {"message": "Pesanan diterima, saldo penjual ditambah"}


@app.patch("/buyer/order-items/{item_id}/complain")
def buyer_complain(
    item_id: int,
    payload: ComplaintIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.user})
    item = db.get(OrderItem, item_id)
    if not item or item.buyer_id != current.id:
        raise HTTPException(status_code=404, detail="Item pesanan tidak ditemukan")
    if item.status != OrderItemStatus.sampai_di_tujuan:
        raise HTTPException(status_code=400, detail="Komplain hanya saat sampai di tujuan")

    old = item.status
    item.status = OrderItemStatus.dikomplain
    item.complaint_reason = payload.reason
    log_status_change(db, item, old, item.status, current.id, payload.reason)
    db.commit()
    return {"message": "Komplain dicatat"}


@app.get("/admin/users", response_model=list[AccountOut])
def admin_list_users(
    current: Account = Depends(get_current_account), db: Session = Depends(get_db)
):
    require_role(current, {RoleType.admin})
    return db.scalars(select(Account).where(Account.role == RoleType.user)).all()


@app.get("/admin/couriers", response_model=list[AccountOut])
def admin_list_couriers(
    current: Account = Depends(get_current_account), db: Session = Depends(get_db)
):
    require_role(current, {RoleType.admin})
    return db.scalars(select(Account).where(Account.role == RoleType.courier)).all()


@app.post("/admin/users", response_model=AccountOut)
def admin_create_user(
    payload: AdminCreateAccountIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.admin})
    exists = db.scalar(
        select(Account).where(
            (Account.username == payload.username) | (Account.email == payload.email)
        )
    )
    if exists:
        raise HTTPException(status_code=400, detail="Username/email sudah dipakai")

    account = Account(
        role=RoleType.user,
        full_name=payload.full_name,
        username=payload.username,
        email=payload.email,
        password_hash=hash_password(payload.password),
        is_active=True,
    )
    db.add(account)
    db.flush()
    ensure_wallet(db, account.id)
    db.commit()
    db.refresh(account)
    return account


@app.post("/admin/couriers", response_model=AccountOut)
def admin_create_courier(
    payload: AdminCreateAccountIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.admin})
    exists = db.scalar(
        select(Account).where(
            (Account.username == payload.username) | (Account.email == payload.email)
        )
    )
    if exists:
        raise HTTPException(status_code=400, detail="Username/email sudah dipakai")

    account = Account(
        role=RoleType.courier,
        full_name=payload.full_name,
        username=payload.username,
        email=payload.email,
        password_hash=hash_password(payload.password),
        is_active=True,
    )
    db.add(account)
    db.flush()
    ensure_wallet(db, account.id)
    db.commit()
    db.refresh(account)
    return account


@app.patch("/admin/accounts/{account_id}", response_model=AccountOut)
def admin_update_account(
    account_id: int,
    payload: AdminUpdateAccountIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.admin})
    target = db.get(Account, account_id)
    if not target or target.role == RoleType.admin:
        raise HTTPException(status_code=404, detail="Akun tidak ditemukan")

    for field, value in payload.model_dump(exclude_none=True).items():
        setattr(target, field, value)
    db.commit()
    db.refresh(target)
    return target


@app.delete("/admin/accounts/{account_id}")
def admin_delete_account(
    account_id: int,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.admin})
    target = db.get(Account, account_id)
    if not target or target.role == RoleType.admin:
        raise HTTPException(status_code=404, detail="Akun tidak ditemukan")
    db.delete(target)
    db.commit()
    return {"message": "Akun dihapus"}


@app.post("/admin/wallets/add", response_model=WalletMutationOut)
def admin_add_balance(
    payload: AdminAdjustBalanceIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.admin})
    target = db.get(Account, payload.account_id)
    if not target or target.role not in {RoleType.user, RoleType.courier}:
        raise HTTPException(status_code=404, detail="Target akun tidak valid")

    wallet = ensure_wallet(db, target.id)
    amount = to_decimal(payload.amount)
    wallet.balance = to_decimal(wallet.balance) + amount
    db.add(wallet)

    mutation = WalletMutation(
        wallet_id=wallet.id,
        direction=MutationDirection.credit,
        amount=amount,
        type=MutationType.admin_adjustment_plus,
        ref_type=MutationRefType.manual,
        description=payload.description,
        created_by_admin_id=current.id,
    )
    db.add(mutation)
    db.commit()
    db.refresh(mutation)
    return WalletMutationOut(
        id=mutation.id,
        wallet_id=mutation.wallet_id,
        direction=mutation.direction.value,
        amount=float(mutation.amount),
        type=mutation.type.value,
        created_at=mutation.created_at,
    )


@app.post("/admin/wallets/deduct", response_model=WalletMutationOut)
def admin_deduct_balance(
    payload: AdminAdjustBalanceIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.admin})
    target = db.get(Account, payload.account_id)
    if not target or target.role not in {RoleType.user, RoleType.courier}:
        raise HTTPException(status_code=404, detail="Target akun tidak valid")

    wallet = ensure_wallet(db, target.id)
    amount = to_decimal(payload.amount)
    if to_decimal(wallet.balance) < amount:
        raise HTTPException(status_code=400, detail="Saldo tidak cukup")

    wallet.balance = to_decimal(wallet.balance) - amount
    db.add(wallet)

    mutation = WalletMutation(
        wallet_id=wallet.id,
        direction=MutationDirection.debit,
        amount=amount,
        type=MutationType.admin_adjustment_minus,
        ref_type=MutationRefType.manual,
        description=payload.description,
        created_by_admin_id=current.id,
    )
    db.add(mutation)
    db.commit()
    db.refresh(mutation)
    return WalletMutationOut(
        id=mutation.id,
        wallet_id=mutation.wallet_id,
        direction=mutation.direction.value,
        amount=float(mutation.amount),
        type=mutation.type.value,
        created_at=mutation.created_at,
    )


@app.patch("/admin/products/{product_id}/status", response_model=ProductOut)
def admin_update_product_status(
    product_id: int,
    payload: AdminUpdateProductStatusIn,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.admin})
    product = db.get(Product, product_id)
    if not product:
        raise HTTPException(status_code=404, detail="Produk tidak ditemukan")

    # Admin hanya boleh ubah status listing.
    product.status = payload.status
    db.commit()
    db.refresh(product)
    return product


@app.delete("/admin/products/{product_id}")
def admin_delete_product(
    product_id: int,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.admin})
    product = db.get(Product, product_id)
    if not product:
        raise HTTPException(status_code=404, detail="Produk tidak ditemukan")
    db.delete(product)
    db.commit()
    return {"message": "Produk listing dihapus admin"}


@app.patch("/admin/order-items/{item_id}/fail")
def admin_mark_transaction_failed(
    item_id: int,
    note: str | None = None,
    current: Account = Depends(get_current_account),
    db: Session = Depends(get_db),
):
    require_role(current, {RoleType.admin})
    raise HTTPException(
        status_code=403,
        detail="Admin tidak diizinkan mengubah status item pesanan",
    )
