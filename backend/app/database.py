from sqlalchemy import create_engine
from sqlalchemy.orm import declarative_base, sessionmaker

# Ganti 'ecommerce_db' dengan nama database yang Anda buat di Laragon
# Format: mysql+pymysql://username:password@host:port/nama_database
DATABASE_URL = "mysql+pymysql://root:@localhost:3306/ecommerce_db"

# Hapus connect_args={"check_same_thread": False} karena itu khusus untuk SQLite
engine = create_engine(DATABASE_URL)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
Base = declarative_base()
