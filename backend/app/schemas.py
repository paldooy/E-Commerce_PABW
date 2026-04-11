from datetime import datetime
from pydantic import BaseModel, ConfigDict, EmailStr, Field

from .models import ListingStatus, OrderItemStatus, RoleType


class TokenOut(BaseModel):
    access_token: str
    token_type: str = "bearer"


class RegisterIn(BaseModel):
    full_name: str = Field(min_length=2, max_length=120)
    username: str = Field(min_length=3, max_length=60)
    email: EmailStr
    password: str = Field(min_length=6, max_length=100)


class LoginIn(BaseModel):
    username: str
    password: str


class AccountOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    role: RoleType
    full_name: str
    username: str
    email: str
    is_active: bool


class WalletOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    wallet_number: str
    balance: float


class ProductCreateIn(BaseModel):
    name: str
    description: str
    image_url: str
    price: float = Field(gt=0)
    stock: int = Field(ge=0)


class ProductUpdateIn(BaseModel):
    name: str | None = None
    description: str | None = None
    image_url: str | None = None
    price: float | None = Field(default=None, gt=0)
    stock: int | None = Field(default=None, ge=0)


class ProductOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    seller_id: int
    name: str
    description: str
    image_url: str
    price: float
    stock: int
    status: ListingStatus


class CartItemIn(BaseModel):
    product_id: int
    quantity: int = Field(gt=0)


class CartItemOut(BaseModel):
    id: int
    product_id: int
    quantity: int
    product_name: str
    unit_price: float
    subtotal: float


class CheckoutOut(BaseModel):
    order_id: int
    order_number: str
    total_amount: float


class OrderItemOut(BaseModel):
    id: int
    order_id: int
    seller_id: int
    buyer_id: int
    courier_id: int | None
    product_name_snapshot: str
    quantity: int
    subtotal: float
    status: OrderItemStatus
    complaint_reason: str | None


class StatusUpdateIn(BaseModel):
    new_status: OrderItemStatus
    note: str | None = None


class ComplaintIn(BaseModel):
    reason: str


class AdminAdjustBalanceIn(BaseModel):
    account_id: int
    amount: float = Field(gt=0)
    description: str | None = None


class AdminCreateAccountIn(BaseModel):
    full_name: str
    username: str
    email: EmailStr
    password: str = Field(min_length=6)


class AdminUpdateAccountIn(BaseModel):
    full_name: str | None = None
    email: EmailStr | None = None
    is_active: bool | None = None


class AdminUpdateProductStatusIn(BaseModel):
    status: ListingStatus


class WalletMutationOut(BaseModel):
    id: int
    wallet_id: int
    direction: str
    amount: float
    type: str
    created_at: datetime
