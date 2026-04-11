from datetime import datetime
from enum import Enum

from sqlalchemy import (
    Boolean,
    DateTime,
    Enum as SqlEnum,
    ForeignKey,
    Integer,
    Numeric,
    String,
    Text,
    UniqueConstraint,
)
from sqlalchemy.orm import Mapped, mapped_column, relationship

from .database import Base


class RoleType(str, Enum):
    user = "user"
    courier = "courier"
    admin = "admin"


class ListingStatus(str, Enum):
    stok_kosong = "stok_kosong"
    stok_tersedia = "stok_tersedia"


class OrderItemStatus(str, Enum):
    menunggu_penjual = "menunggu_penjual"
    diproses_penjual = "diproses_penjual"
    menunggu_kurir = "menunggu_kurir"
    sedang_dikirim = "sedang_dikirim"
    sampai_di_tujuan = "sampai_di_tujuan"
    diterima_pembeli = "diterima_pembeli"
    dikomplain = "dikomplain"
    dikirim_balik = "dikirim_balik"
    transaksi_gagal = "transaksi_gagal"


class MutationDirection(str, Enum):
    debit = "debit"
    credit = "credit"


class MutationType(str, Enum):
    checkout_debit = "checkout_debit"
    seller_payout = "seller_payout"
    full_refund = "full_refund"
    admin_adjustment_plus = "admin_adjustment_plus"
    admin_adjustment_minus = "admin_adjustment_minus"


class MutationRefType(str, Enum):
    order = "order"
    order_item = "order_item"
    manual = "manual"


class EscrowStatus(str, Enum):
    funded = "funded"
    released = "released"
    refunded = "refunded"


class Account(Base):
    __tablename__ = "accounts"

    id: Mapped[int] = mapped_column(primary_key=True, index=True)
    role: Mapped[RoleType] = mapped_column(SqlEnum(RoleType), nullable=False)
    full_name: Mapped[str] = mapped_column(String(120), nullable=False)
    username: Mapped[str] = mapped_column(String(60), nullable=False, unique=True)
    email: Mapped[str] = mapped_column(String(120), nullable=False, unique=True)
    password_hash: Mapped[str] = mapped_column(String(255), nullable=False)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, default=datetime.utcnow, onupdate=datetime.utcnow
    )

    wallet = relationship("Wallet", back_populates="account", uselist=False)


class Wallet(Base):
    __tablename__ = "wallets"

    id: Mapped[int] = mapped_column(primary_key=True, index=True)
    account_id: Mapped[int] = mapped_column(ForeignKey("accounts.id"), unique=True)
    wallet_number: Mapped[str] = mapped_column(String(30), unique=True, nullable=False)
    balance: Mapped[float] = mapped_column(Numeric(18, 2), default=0)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, default=datetime.utcnow, onupdate=datetime.utcnow
    )

    account = relationship("Account", back_populates="wallet")


class WalletMutation(Base):
    __tablename__ = "wallet_mutations"

    id: Mapped[int] = mapped_column(primary_key=True, index=True)
    wallet_id: Mapped[int] = mapped_column(ForeignKey("wallets.id"), nullable=False)
    direction: Mapped[MutationDirection] = mapped_column(
        SqlEnum(MutationDirection), nullable=False
    )
    amount: Mapped[float] = mapped_column(Numeric(18, 2), nullable=False)
    type: Mapped[MutationType] = mapped_column(SqlEnum(MutationType), nullable=False)
    ref_type: Mapped[MutationRefType] = mapped_column(
        SqlEnum(MutationRefType), default=MutationRefType.manual
    )
    ref_id: Mapped[int | None] = mapped_column(nullable=True)
    description: Mapped[str | None] = mapped_column(Text, nullable=True)
    created_by_admin_id: Mapped[int | None] = mapped_column(
        ForeignKey("accounts.id"), nullable=True
    )
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)


class Product(Base):
    __tablename__ = "products"

    id: Mapped[int] = mapped_column(primary_key=True, index=True)
    seller_id: Mapped[int] = mapped_column(ForeignKey("accounts.id"), nullable=False)
    name: Mapped[str] = mapped_column(String(150), nullable=False)
    description: Mapped[str] = mapped_column(Text, nullable=False)
    image_url: Mapped[str] = mapped_column(Text, nullable=False)
    price: Mapped[float] = mapped_column(Numeric(18, 2), nullable=False)
    stock: Mapped[int] = mapped_column(Integer, default=0)
    status: Mapped[ListingStatus] = mapped_column(
        SqlEnum(ListingStatus), default=ListingStatus.stok_kosong
    )
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, default=datetime.utcnow, onupdate=datetime.utcnow
    )


class Cart(Base):
    __tablename__ = "carts"

    id: Mapped[int] = mapped_column(primary_key=True, index=True)
    buyer_id: Mapped[int] = mapped_column(ForeignKey("accounts.id"), unique=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, default=datetime.utcnow, onupdate=datetime.utcnow
    )


class CartItem(Base):
    __tablename__ = "cart_items"
    __table_args__ = (UniqueConstraint("cart_id", "product_id", name="uq_cart_product"),)

    id: Mapped[int] = mapped_column(primary_key=True, index=True)
    cart_id: Mapped[int] = mapped_column(ForeignKey("carts.id"), nullable=False)
    product_id: Mapped[int] = mapped_column(ForeignKey("products.id"), nullable=False)
    quantity: Mapped[int] = mapped_column(Integer, nullable=False)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, default=datetime.utcnow, onupdate=datetime.utcnow
    )


class Order(Base):
    __tablename__ = "orders"

    id: Mapped[int] = mapped_column(primary_key=True, index=True)
    order_number: Mapped[str] = mapped_column(String(40), unique=True, nullable=False)
    buyer_id: Mapped[int] = mapped_column(ForeignKey("accounts.id"), nullable=False)
    total_amount: Mapped[float] = mapped_column(Numeric(18, 2), nullable=False)
    escrow_status: Mapped[EscrowStatus] = mapped_column(
        SqlEnum(EscrowStatus), default=EscrowStatus.funded
    )
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)
    paid_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)
    released_at: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)
    refunded_at: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)


class OrderItem(Base):
    __tablename__ = "order_items"

    id: Mapped[int] = mapped_column(primary_key=True, index=True)
    order_id: Mapped[int] = mapped_column(ForeignKey("orders.id"), nullable=False)
    product_id: Mapped[int | None] = mapped_column(ForeignKey("products.id"), nullable=True)
    seller_id: Mapped[int] = mapped_column(ForeignKey("accounts.id"), nullable=False)
    buyer_id: Mapped[int] = mapped_column(ForeignKey("accounts.id"), nullable=False)
    courier_id: Mapped[int | None] = mapped_column(ForeignKey("accounts.id"), nullable=True)
    product_name_snapshot: Mapped[str] = mapped_column(String(150), nullable=False)
    product_description_snapshot: Mapped[str] = mapped_column(Text, nullable=False)
    product_image_snapshot: Mapped[str] = mapped_column(Text, nullable=False)
    unit_price_snapshot: Mapped[float] = mapped_column(Numeric(18, 2), nullable=False)
    quantity: Mapped[int] = mapped_column(Integer, nullable=False)
    subtotal: Mapped[float] = mapped_column(Numeric(18, 2), nullable=False)
    status: Mapped[OrderItemStatus] = mapped_column(
        SqlEnum(OrderItemStatus), default=OrderItemStatus.menunggu_penjual
    )
    complaint_reason: Mapped[str | None] = mapped_column(Text, nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, default=datetime.utcnow, onupdate=datetime.utcnow
    )
    failed_at: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)


class OrderItemStatusLog(Base):
    __tablename__ = "order_item_status_logs"

    id: Mapped[int] = mapped_column(primary_key=True, index=True)
    order_item_id: Mapped[int] = mapped_column(ForeignKey("order_items.id"), nullable=False)
    old_status: Mapped[OrderItemStatus | None] = mapped_column(
        SqlEnum(OrderItemStatus), nullable=True
    )
    new_status: Mapped[OrderItemStatus] = mapped_column(
        SqlEnum(OrderItemStatus), nullable=False
    )
    changed_by_account_id: Mapped[int] = mapped_column(
        ForeignKey("accounts.id"), nullable=False
    )
    note: Mapped[str | None] = mapped_column(Text, nullable=True)
    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow)
