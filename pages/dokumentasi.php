<?php
session_start();
require '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$page_title = "Dokumentasi API";
require '../komponen/header.php';
require '../komponen/navbar.php';
?>

<!-- Content -->
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Dokumentasi API</h4>
    <!-- Button trigger modal -->
    <button type="button" class="btn btn-info my-3" data-bs-toggle="modal" data-bs-target="#cliExampleModal">
    Lihat Contoh CLI Python
    </button>

    <!-- Modal -->
    <div class="modal fade" id="cliExampleModal" tabindex="-1" aria-labelledby="cliExampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="cliExampleModalLabel">Contoh Kode CLI Python - License Manager</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
            <pre style="white-space: pre-wrap; word-break: break-word; font-size: 13px; background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
    <code>
    import requests
    import json
    import sys
    from datetime import datetime
    from prettytable import PrettyTable
    from colorama import Fore, Style, init

    # Inisialisasi colorama
    init(autoreset=True)

    # Konfigurasi
    BASE_URL = "http://localhost/lisensimanager/api"
    ADMIN_TOKEN = "2e52971b72004c2555b8b59dda0108ae9e157a5d17cbe434a457cffd55c42e3c"

    def clear_screen():
        """Membersihkan layar console"""
        print("\n" * 100)

    def print_header(title):
        """Menampilkan header menu"""
        clear_screen()
        print(Fore.CYAN + "=" * 50)
        print(f"{title:^50}")
        print("=" * 50 + Style.RESET_ALL)
        print()

    def press_enter_to_continue():
        """Menunggu user menekan enter"""
        input("\nTekan Enter untuk melanjutkan...")

    def print_success(message):
        """Menampilkan pesan sukses"""
        print(Fore.GREEN + f"[SUCCESS] {message}" + Style.RESET_ALL)

    def print_error(message):
        """Menampilkan pesan error"""
        print(Fore.RED + f"[ERROR] {message}" + Style.RESET_ALL)

    def print_warning(message):
        """Menampilkan pesan warning"""
        print(Fore.YELLOW + f"[WARNING] {message}" + Style.RESET_ALL)

    def get_products():
        """Mengambil daftar produk dari API dengan GET request"""
        url = f"{BASE_URL}/list_products.php"
        headers = {
            'Authorization': f'Bearer {ADMIN_TOKEN}',
            'Accept': 'application/json'
        }
        params = {
            'token': ADMIN_TOKEN
        }

        try:
            # Debug info
            # print(f"\nMengirim GET request ke: {url}")
            # print(f"Headers: {headers}")
            # print(f"Params: {params}")

            response = requests.get(
                url,
                headers=headers,
                params=params,
                timeout=10
            )

            # Debug response
            # print(f"\nStatus Code: {response.status_code}")
            # print(f"Response Headers: {response.headers}")
            # print(f"Raw Response: {response.text[:200]}...")  # Print first 200 chars

            if response.status_code != 200:
                print_error(f"Error: HTTP {response.status_code}")
                return []

            try:
                data = response.json()
                if data.get("status") == "success":
                    return data.get("data", [])
                else:
                    print_error(f"API Error: {data.get('message', 'Unknown error')}")
                    return []
            except ValueError as e:
                print_error(f"Invalid JSON response: {str(e)}")
                return []

        except requests.exceptions.RequestException as e:
            print_error(f"Request failed: {str(e)}")
            return []

    def list_products():
        """Menampilkan daftar produk"""
        print_header("DAFTAR PRODUK")
        
        print("Mengambil data produk dari server...")
        products = get_products()
        
        if not products:
            print_warning("Tidak ada produk yang tersedia di sistem")
            press_enter_to_continue()
            return
        
        table = PrettyTable()
        table.field_names = ["ID", "Nama Produk", "Tanggal Dibuat"]
        table.align = "l"
        
        for product in products:
            table.add_row([
                product.get('id', 'N/A'),
                product.get('name', 'N/A'),
                product.get('created_at', 'N/A')
            ])
        
        print("\nDaftar Produk:")
        print(table)
        print(f"\nTotal Produk: {len(products)}")
        
        press_enter_to_continue()

    def display_products_table():
        """Menampilkan tabel produk"""
        products = get_products()
        if not products:
            print_warning("Tidak ada produk yang tersedia")
            return
        
        table = PrettyTable()
        table.field_names = ["ID", "Nama Produk", "Tanggal Dibuat"]
        table.align = "l"
        
        for product in products:
            table.add_row([
                product.get('id', 'N/A'),
                product.get('name', 'N/A'),
                product.get('created_at', 'N/A')
            ])
        
        print("\nDaftar Produk:")
        print(table)

    def validate_license():
        """Validasi lisensi"""
        print_header("VALIDASI LISENSI")
        
        license_key = input("Masukkan License Key: ")
        device_code = input("Masukkan Device Code: ")
        
        url = f"{BASE_URL}/check_license.php"
        params = {
            "license_key": license_key,
            "device_code": device_code
        }
        
        try:
            response = requests.post(url, data=params)
            data = response.json()
            
            if data.get("status") == "success":
                license_data = data["data"]
                
                table = PrettyTable()
                table.field_names = ["Field", "Value"]
                table.align["Field"] = "l"
                table.align["Value"] = "l"
                
                table.add_row(["ID", license_data["id"]])
                table.add_row(["License Key", license_data["license_key"]])
                table.add_row(["Nama", license_data["name"]])
                table.add_row(["Expiration Date", license_data["expiration_date"]])
                table.add_row(["Device Code", license_data["device_code"]])
                table.add_row(["Trial", "Ya" if license_data["is_trial"] == 1 else "Tidak"])
                table.add_row(["Product ID", license_data["product_id"]])
                table.add_row(["Product Name", license_data["product_name"]])
                
                print("\nDetail Lisensi:")
                print(table)
            else:
                print_error(data.get("message", "Gagal validasi lisensi"))
        
        except Exception as e:
            print_error(f"Terjadi kesalahan: {str(e)}")
        
        press_enter_to_continue()

    def add_license():
        """Menambahkan lisensi baru"""
        print_header("TAMBAH LISENSI BARU")
        
        # Tampilkan daftar produk
        display_products_table()
        
        name = input("\nNama Lisensi: ")
        device_code = input("Device Code: ")
        
        # Input trial dengan validasi
        while True:
            is_trial = input("Trial (y=Ya, n=Tidak): ").lower()
            if is_trial in ['y', 'n']:
                is_trial = 1 if is_trial == 'y' else 0
                break
            print_warning("Masukkan 'y' untuk Ya atau 'n' untuk Tidak")
        
        duration = None
        if is_trial == 0:
            # Hanya minta durasi jika bukan trial
            while True:
                duration = input("Durasi (Bulanan/Mingguan/Harian): ").capitalize()
                if duration in ['Bulanan', 'Mingguan', 'Harian']:
                    break
                print_warning("Masukkan Bulanan, Mingguan, atau Harian")
        
        # Input product_id dengan validasi
        products = get_products()
        product_ids = [str(p['id']) for p in products]
        
        while True:
            product_id = input("Product ID: ")
            if product_id in product_ids:
                break
            print_warning(f"Product ID tidak valid. Pilih dari: {', '.join(product_ids)}")
        
        url = f"{BASE_URL}/add_license.php?token={ADMIN_TOKEN}"
        params = {
            "name": name,
            "device_code": device_code,
            "is_trial": is_trial,
            "product_id": product_id
        }
        
        if duration:
            params["duration"] = duration
        
        try:
            response = requests.post(url, data=params)
            data = response.json()
            
            if data.get("status") == "success":
                print_success("Lisensi berhasil ditambahkan!")
            else:
                print_error(data.get("message", "Gagal menambahkan lisensi"))
        
        except Exception as e:
            print_error(f"Terjadi kesalahan: {str(e)}")
        
        press_enter_to_continue()

    def list_licenses():
        """Menampilkan daftar lisensi"""
        print_header("DAFTAR LISENSI")
        
        url = f"{BASE_URL}/list_licenses.php?token={ADMIN_TOKEN}"
        
        try:
            response = requests.get(url)
            data = response.json()
            
            if data.get("status") == "success":
                licenses = data.get("data", [])
                
                if not licenses:
                    print_warning("Tidak ada lisensi yang ditemukan")
                else:
                    table = PrettyTable()
                    table.field_names = ["ID", "License Key", "Nama", "Device Code", "Expiration", "Trial", "Product ID"]
                    table.align = "l"
                    
                    for license in licenses:
                        table.add_row([
                            license['id'],
                            license['license_key'],
                            license['name'],
                            license['device_code'],
                            license['expiration_date'],
                            "Ya" if license['is_trial'] == 1 else "Tidak",
                            license['product_id']
                        ])
                    
                    print(table)
            else:
                print_error(data.get("message", "Gagal mengambil daftar lisensi"))
        
        except Exception as e:
            print_error(f"Terjadi kesalahan: {str(e)}")
        
        press_enter_to_continue()

    def modify_license():
        """Memodifikasi lisensi dengan penanganan error yang lebih baik"""
        print_header("MODIFIKASI LISENSI")
        
        try:
            # Tampilkan daftar lisensi
            print("Daftar Lisensi:")
            list_licenses()
            
            license_id = input("\nID Lisensi yang akan dimodifikasi: ")
            if not license_id.isdigit():
                raise ValueError("ID Lisensi harus berupa angka")
                
            # Tampilkan produk yang tersedia
            display_products_table()
            
            # Dapatkan input perubahan
            changes = {}
            name = input("\nNama Baru (kosongkan jika tidak diubah): ").strip()
            if name: changes["name"] = name
            
            device_code = input("Device Code Baru (kosongkan jika tidak diubah): ").strip()
            if device_code: changes["device_code"] = device_code
            
            # Input trial
            trial_input = input("Trial (y=Ya, n=Tidak, kosongkan jika tidak diubah): ").lower()
            if trial_input in ['y', 'n']:
                changes["is_trial"] = 1 if trial_input == 'y' else 0
            
            # Input tanggal kedaluwarsa
            expiration_date = input("Tanggal Kedaluwarsa (YYYY-MM-DD HH:MM:SS, kosongkan jika tidak diubah): ").strip()
            if expiration_date:
                try:
                    datetime.strptime(expiration_date, '%Y-%m-%d %H:%M:%S')
                    changes["expiration_date"] = expiration_date
                except ValueError:
                    print_warning("Format tanggal tidak valid, perubahan tidak disimpan")
            
            # Input product_id
            products = get_products()
            if products:
                product_ids = [str(p['id']) for p in products]
                pid_input = input("Product ID Baru (kosongkan jika tidak diubah): ").strip()
                if pid_input:
                    if pid_input in product_ids:
                        changes["product_id"] = pid_input
                    else:
                        print_warning(f"Product ID tidak valid. Pilih dari: {', '.join(product_ids)}")
            
            # Jika tidak ada perubahan
            if not changes:
                print_warning("Tidak ada perubahan yang dimasukkan")
                press_enter_to_continue()
                return
            
            # Kirim request
            url = f"{BASE_URL}/modify_license.php?token={ADMIN_TOKEN}"
            params = {"id": license_id, **changes}
            
            print(f"\nMengirim perubahan: {params}")  # Debug
            
            response = requests.post(url, data=params)
            
            # Debugging response
            print(f"Status Code: {response.status_code}")
            print(f"Response Content: {response.text}")
            
            if not response.text.strip():
                raise ValueError("Empty response from server")
                
            data = response.json()
            
            if data.get("status") == "success":
                print_success("Lisensi berhasil diupdate!")
            else:
                print_error(data.get("message", "Gagal mengupdate lisensi"))
        
        except ValueError as e:
            print_error(f"Invalid JSON response: {str(e)}")
            if 'response' in locals():
                print(f"Raw response: {response.text}")
        except requests.exceptions.RequestException as e:
            print_error(f"Koneksi error: {str(e)}")
        except Exception as e:
            print_error(f"Terjadi kesalahan: {str(e)}")
        
        press_enter_to_continue()

    def delete_license():
        """Menghapus lisensi"""
        print_header("HAPUS LISENSI")
        
        # Tampilkan daftar lisensi terlebih dahulu
        print("Daftar Lisensi:")
        list_licenses()
        
        license_id = input("\nID Lisensi yang akan dihapus: ")
        
        # Konfirmasi penghapusan
        confirm = input(f"Apakah Anda yakin ingin menghapus lisensi ID {license_id}? (y/n): ").lower()
        if confirm != 'y':
            print_warning("Penghapusan dibatalkan")
            press_enter_to_continue()
            return
        
        url = f"{BASE_URL}/delete_license.php?token={ADMIN_TOKEN}"
        params = {"id": license_id}
        
        try:
            response = requests.post(url, data=params)
            data = response.json()
            
            if data.get("status") == "success":
                print_success("Lisensi berhasil dihapus!")
            else:
                print_error(data.get("message", "Gagal menghapus lisensi"))
        
        except Exception as e:
            print_error(f"Terjadi kesalahan: {str(e)}")
        
        press_enter_to_continue()

    def add_product():
        """Menambahkan produk baru dengan penanganan error yang lebih baik"""
        print_header("TAMBAH PRODUK BARU")
        
        name = input("Nama Produk: ").strip()
        
        if not name:
            print_error("Nama produk tidak boleh kosong")
            press_enter_to_continue()
            return
        
        url = f"{BASE_URL}/add_products.php?token={ADMIN_TOKEN}"
        params = {"name": name}
        
        try:
            response = requests.post(url, data=params, timeout=10)
            
            # Debugging output
            # print(f"Status Code: {response.status_code}")
            # print(f"Response Content: {response.text}")
            
            # Check if response is empty
            if not response.text.strip():
                raise ValueError("Empty response from server")
                
            data = response.json()
            
            if data.get("status") == "success":
                print_success("Produk berhasil ditambahkan!")
                print(f"ID Produk: {data.get('product_id', 'N/A')}")
            else:
                print_error(data.get("message", "Gagal menambahkan produk"))
        
        except ValueError as e:
            print_error(f"Invalid JSON response: {str(e)}")
            if 'response' in locals():
                print(f"Raw response: {response.text}")
        except requests.exceptions.RequestException as e:
            print_error(f"Koneksi error: {str(e)}")
        except Exception as e:
            print_error(f"Terjadi kesalahan: {str(e)}")
        
        press_enter_to_continue()

    def delete_product():
        """Menghapus produk dengan penanganan constraint foreign key"""
        print_header("HAPUS PRODUK")
        
        # Tampilkan daftar produk
        display_products_table()
        
        product_id = input("\nID Produk yang akan dihapus: ")
        
        # Konfirmasi penghapusan
        confirm = input(f"Apakah Anda yakin ingin menghapus produk ID {product_id}? (y/n): ").lower()
        if confirm != 'y':
            print_warning("Penghapusan dibatalkan")
            press_enter_to_continue()
            return
        
        url = f"{BASE_URL}/delete_product.php?token={ADMIN_TOKEN}"
        params = {"id": product_id}
        
        try:
            response = requests.post(url, data=params)
            data = response.json()
            
            if data.get("status") == "success":
                print_success("Produk berhasil dihapus!")
            elif data.get("error", "").find("foreign key constraint") != -1:
                print_error("Tidak dapat menghapus produk karena masih digunakan di tabel lisensi")
                print_warning("Hapus terlebih dahulu semua lisensi yang menggunakan produk ini")
            else:
                print_error(data.get("message", "Gagal menghapus produk"))
        
        except Exception as e:
            print_error(f"Terjadi kesalahan: {str(e)}")
        
        press_enter_to_continue()

    def main_menu():
        """Menu utama"""
        while True:
            clear_screen()
            print(Fore.CYAN + "=" * 50)
            print("LICENSE MANAGER CLI TOOL".center(50))
            print("=" * 50 + Style.RESET_ALL)
            print("\nMenu Utama:")
            print("1. Validasi Lisensi")
            print("2. Daftar Lisensi Baru")
            print("3. List Lisensi")
            print("4. List Produk")  # Menu baru
            print("5. Modifikasi Lisensi")
            print("6. Hapus Lisensi")
            print("7. Tambah Produk Baru")
            print("8. Hapus Produk")
            print("0. Keluar")
            
            choice = input("\nPilih menu: ")
            
            if choice == "1":
                validate_license()
            elif choice == "2":
                add_license()
            elif choice == "3":
                list_licenses()
            elif choice == "4":  # Pilihan baru
                list_products()
            elif choice == "5":
                modify_license()
            elif choice == "6":
                delete_license()
            elif choice == "7":
                add_product()
            elif choice == "8":
                delete_product()
            elif choice == "0":
                print("\nTerima kasih telah menggunakan License Manager CLI Tool!")
                sys.exit()
            else:
                print_warning("Pilihan tidak valid!")
                press_enter_to_continue()

    if __name__ == "__main__":
        try:
            main_menu()
        except KeyboardInterrupt:
            print("\n\nProgram dihentikan oleh pengguna")
            sys.exit()
    </code>
        </pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
<!-- Button trigger modal -->
<button type="button" class="btn btn-success my-3" data-bs-toggle="modal" data-bs-target="#telegramBotModal">
  Lihat Contoh Bot Telegram Python
</button>

<!-- Modal -->
<div class="modal fade" id="telegramBotModal" tabindex="-1" aria-labelledby="telegramBotModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="telegramBotModalLabel">Contoh Kode Bot Telegram Python</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <pre style="white-space: pre-wrap; word-break: break-word; font-size: 13px; background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
<code>
import requests
from telegram import Update, ReplyKeyboardMarkup, ReplyKeyboardRemove
from telegram.ext import (
    ApplicationBuilder, CommandHandler, MessageHandler,
    ConversationHandler, ContextTypes, filters
)
from datetime import datetime

# Konfigurasi bot
TOKEN = '8130489744:AAF6c46zwzEDN8pvhdFbKnlI9CO7mds3swk'
BASE_URL = 'http://localhost/lisensimanager/api'
ADMIN_TOKEN = '2e52971b72004c2555b8b59dda0108ae9e157a5d17cbe434a457cffd55c42e3c'
ADMIN_ID = 6994514050

# State conversation
(
    MENU,
    VALIDATE_LICENSE,
    ADD_LICENSE,
    DELETE_LICENSE,
    MODIFY_LICENSE,
    ADD_PRODUCT,
    DELETE_PRODUCT,
    ASK_LICENSE_INFO,
    ASK_TRIAL,
    ASK_DURATION,
    MODIFY_LICENSE_ID,
    MODIFY_LICENSE_FIELD
) = range(12)

# Fungsi utilitas
def is_admin(update: Update) -> bool:
    return update.effective_user.id == ADMIN_ID

def get_main_menu():
    return ReplyKeyboardMarkup([
        ['✅ Validasi Lisensi', '➕ Tambah Lisensi Baru'],
        ['📋 List Lisensi', '📦 List Produk'],
        ['✏️ Modifikasi Lisensi', '🗑 Hapus Lisensi'],
        ['🆕 Tambah Produk', '❌ Hapus Produk'],
        ['🚫 Cancel']
    ], resize_keyboard=True)

# /start
async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    if not is_admin(update):
        await update.message.reply_text("❌ Kamu tidak memiliki akses ke bot ini.")
        return ConversationHandler.END
    await update.message.reply_text(
        "👋 Selamat datang di License Manager Bot!\nSilakan pilih menu di bawah ini:",
        reply_markup=get_main_menu()
    )
    return MENU

# Menu handler
async def menu_handler(update: Update, context: ContextTypes.DEFAULT_TYPE):
    if not is_admin(update):
        await update.message.reply_text("❌ Akses ditolak.")
        return ConversationHandler.END

    choice = update.message.text

    if choice == '✅ Validasi Lisensi':
        await update.message.reply_text("Ketik format:\nlicense_key,device_code", reply_markup=ReplyKeyboardMarkup([["🚫 Cancel"]], resize_keyboard=True))
        return VALIDATE_LICENSE
    elif choice == '➕ Tambah Lisensi Baru':
        await update.message.reply_text("Masukkan nama lisensi:", reply_markup=ReplyKeyboardMarkup([["🚫 Cancel"]], resize_keyboard=True))
        return ASK_LICENSE_INFO
    elif choice == '📋 List Lisensi':
        await list_licenses(update)
        return MENU
    elif choice == '📦 List Produk':
        await list_products(update)
        return MENU
    elif choice == '✏️ Modifikasi Lisensi':
        await update.message.reply_text("Masukkan ID lisensi yang ingin dimodifikasi:", reply_markup=ReplyKeyboardMarkup([["🚫 Cancel"]], resize_keyboard=True))
        return MODIFY_LICENSE_ID
    elif choice == '🗑 Hapus Lisensi':
        await update.message.reply_text("Masukkan ID lisensi yang ingin dihapus:", reply_markup=ReplyKeyboardMarkup([["🚫 Cancel"]], resize_keyboard=True))
        return DELETE_LICENSE
    elif choice == '🆕 Tambah Produk':
        await update.message.reply_text("Masukkan nama produk:", reply_markup=ReplyKeyboardMarkup([["🚫 Cancel"]], resize_keyboard=True))
        return ADD_PRODUCT
    elif choice == '❌ Hapus Produk':
        await update.message.reply_text("Masukkan ID produk yang ingin dihapus:", reply_markup=ReplyKeyboardMarkup([["🚫 Cancel"]], resize_keyboard=True))
        return DELETE_PRODUCT
    elif choice == '🚫 Cancel':
        await update.message.reply_text("Dibatalkan.", reply_markup=get_main_menu())
        return MENU
    return MENU

# Validasi lisensi
async def validate_license(update: Update, context: ContextTypes.DEFAULT_TYPE):
    try:
        license_key, device_code = update.message.text.strip().split(',')
        url = f"{BASE_URL}/check_license.php"
        response = requests.post(url, data={"license_key": license_key, "device_code": device_code}).json()
        if response.get("status") == "success":
            data = response["data"]
            msg = (
                f"✅ Validasi Berhasil:\n"
                f"Nama: {data['name']}\n"
                f"Produk: {data['product_name']}\n"
                f"Exp: {data['expiration_date']}\n"
                f"Trial: {'Ya' if data['is_trial'] else 'Tidak'}"
            )
        else:
            msg = f"❌ Gagal: {response.get('message')}"
    except Exception:
        msg = "Format salah. Gunakan: license_key,device_code"
    await update.message.reply_text(msg, reply_markup=get_main_menu())
    return MENU

# Tambah lisensi - langkah 1
async def ask_license_info(update: Update, context: ContextTypes.DEFAULT_TYPE):
    context.user_data['name'] = update.message.text.strip()
    await update.message.reply_text("Masukkan device code:", reply_markup=ReplyKeyboardMarkup([["🚫 Cancel"]], resize_keyboard=True))
    return ASK_TRIAL

# Tambah lisensi - langkah 2
async def ask_trial(update: Update, context: ContextTypes.DEFAULT_TYPE):
    if 'device_code' not in context.user_data:
        context.user_data['device_code'] = update.message.text.strip()
        await update.message.reply_text("Lisensi trial?\nPilih:", reply_markup=ReplyKeyboardMarkup([["Ya (Trial)", "Tidak (Non-Trial)"], ["🚫 Cancel"]], resize_keyboard=True))
        return ASK_TRIAL

    choice = update.message.text.lower()
    if "ya" in choice:
        context.user_data['is_trial'] = 1
        context.user_data['duration'] = "1"
        await update.message.reply_text("Masukkan ID produk:", reply_markup=ReplyKeyboardMarkup([["🚫 Cancel"]], resize_keyboard=True))
        return ADD_LICENSE
    elif "tidak" in choice:
        context.user_data['is_trial'] = 0
        await update.message.reply_text("Masukkan durasi (contoh: 30 untuk 30 hari):", reply_markup=ReplyKeyboardMarkup([["🚫 Cancel"]], resize_keyboard=True))
        return ASK_DURATION
    else:
        await update.message.reply_text("Pilih 'Ya (Trial)' atau 'Tidak (Non-Trial)'")
        return ASK_TRIAL

# Tambah lisensi - durasi
async def ask_duration(update: Update, context: ContextTypes.DEFAULT_TYPE):
    context.user_data['duration'] = update.message.text.strip()
    await update.message.reply_text("Masukkan ID produk:", reply_markup=ReplyKeyboardMarkup([["🚫 Cancel"]], resize_keyboard=True))
    return ADD_LICENSE

# Submit lisensi
async def add_license(update: Update, context: ContextTypes.DEFAULT_TYPE):
    context.user_data['product_id'] = update.message.text.strip()
    params = {
        "name": context.user_data['name'],
        "device_code": context.user_data['device_code'],
        "is_trial": context.user_data['is_trial'],
        "product_id": context.user_data['product_id']
    }
    if context.user_data['is_trial'] == 0:
        params["duration"] = context.user_data['duration']
    try:
        url = f"{BASE_URL}/add_license.php?token={ADMIN_TOKEN}"
        response = requests.post(url, data=params).json()
        msg = "✅ Lisensi berhasil ditambahkan!" if response.get("status") == "success" else f"❌ Gagal: {response.get('message')}"
    except Exception as e:
        msg = f"❌ Error: {str(e)}"
    await update.message.reply_text(msg, reply_markup=get_main_menu())
    return MENU

# Modifikasi lisensi - input ID
async def modify_license_id(update: Update, context: ContextTypes.DEFAULT_TYPE):
    license_id = update.message.text.strip()
    if not license_id.isdigit():
        await update.message.reply_text("❌ ID lisensi harus angka.", reply_markup=get_main_menu())
        return MENU
    context.user_data['license_id'] = license_id
    await update.message.reply_text(
        "Masukkan perubahan (hanya yang ingin diubah):\n"
        "Contoh:\nnama=John,device_code=12345,is_trial=0,expiration_date=2025-01-01 12:00:00,product_id=2",
        reply_markup=ReplyKeyboardMarkup([["🚫 Cancel"]], resize_keyboard=True)
    )
    return MODIFY_LICENSE_FIELD

# Modifikasi lisensi - apply changes
async def modify_license_field(update: Update, context: ContextTypes.DEFAULT_TYPE):
    try:
        text = update.message.text.strip()
        data = dict(i.split('=') for i in text.split(',') if '=' in i and i.split('=')[1])
        if 'expiration_date' in data:
            datetime.strptime(data['expiration_date'], '%Y-%m-%d %H:%M:%S')
        data['id'] = context.user_data['license_id']
        url = f"{BASE_URL}/modify_license.php?token={ADMIN_TOKEN}"
        response = requests.post(url, data=data).json()
        msg = "✅ Lisensi berhasil dimodifikasi!" if response.get("status") == "success" else f"❌ Gagal: {response.get('message')}"
    except Exception as e:
        msg = f"❌ Terjadi kesalahan: {str(e)}"
    await update.message.reply_text(msg, reply_markup=get_main_menu())
    return MENU

# List produk
async def list_products(update: Update):
    url = f"{BASE_URL}/list_products.php?token={ADMIN_TOKEN}"
    response = requests.get(url).json()
    if response.get("status") == "success":
        data = response.get("data", [])
        msg = '\n'.join([f"{p['id']}. {p['name']}" for p in data]) or "Tidak ada produk."
    else:
        msg = "❌ Gagal mengambil produk."
    await update.message.reply_text(msg)

# List lisensi
async def list_licenses(update: Update):
    url = f"{BASE_URL}/list_licenses.php?token={ADMIN_TOKEN}"
    response = requests.get(url).json()
    if response.get("status") == "success":
        data = response.get("data", [])
        msg = '\n'.join([f"{l['id']}. {l['name']} | {l['license_key']} | Exp: {l['expiration_date']}" for l in data]) or "Tidak ada lisensi."
    else:
        msg = "❌ Gagal mengambil lisensi."
    await update.message.reply_text(msg)

# Tambah produk
async def add_product(update: Update, context: ContextTypes.DEFAULT_TYPE):
    name = update.message.text.strip()
    url = f"{BASE_URL}/add_products.php?token={ADMIN_TOKEN}"
    response = requests.post(url, data={"name": name}).json()
    msg = "✅ Produk ditambahkan!" if response.get("status") == "success" else f"❌ Gagal: {response.get('message')}"
    await update.message.reply_text(msg, reply_markup=get_main_menu())
    return MENU

# Hapus lisensi
async def delete_license(update: Update, context: ContextTypes.DEFAULT_TYPE):
    license_id = update.message.text.strip()
    url = f"{BASE_URL}/delete_license.php?token={ADMIN_TOKEN}"
    response = requests.post(url, data={"id": license_id}).json()
    msg = "✅ Lisensi dihapus." if response.get("status") == "success" else f"❌ Gagal: {response.get('message')}"
    await update.message.reply_text(msg, reply_markup=get_main_menu())
    return MENU

# Hapus produk
async def delete_product(update: Update, context: ContextTypes.DEFAULT_TYPE):
    product_id = update.message.text.strip()
    url = f"{BASE_URL}/delete_product.php?token={ADMIN_TOKEN}"
    response = requests.post(url, data={"id": product_id}).json()
    if "foreign key" in str(response).lower():
        msg = "⚠️ Produk tidak bisa dihapus karena masih digunakan."
    else:
        msg = "✅ Produk dihapus." if response.get("status") == "success" else f"❌ Gagal: {response.get('message')}"
    await update.message.reply_text(msg, reply_markup=get_main_menu())
    return MENU

# Main
def main():
    app = ApplicationBuilder().token(TOKEN).build()
    conv_handler = ConversationHandler(
        entry_points=[CommandHandler('start', start)],
        states={
            MENU: [MessageHandler(filters.TEXT & ~filters.COMMAND, menu_handler)],
            VALIDATE_LICENSE: [MessageHandler(filters.TEXT & ~filters.COMMAND, validate_license)],
            ASK_LICENSE_INFO: [MessageHandler(filters.TEXT & ~filters.COMMAND, ask_license_info)],
            ASK_TRIAL: [MessageHandler(filters.TEXT & ~filters.COMMAND, ask_trial)],
            ASK_DURATION: [MessageHandler(filters.TEXT & ~filters.COMMAND, ask_duration)],
            ADD_LICENSE: [MessageHandler(filters.TEXT & ~filters.COMMAND, add_license)],
            ADD_PRODUCT: [MessageHandler(filters.TEXT & ~filters.COMMAND, add_product)],
            DELETE_LICENSE: [MessageHandler(filters.TEXT & ~filters.COMMAND, delete_license)],
            DELETE_PRODUCT: [MessageHandler(filters.TEXT & ~filters.COMMAND, delete_product)],
            MODIFY_LICENSE_ID: [MessageHandler(filters.TEXT & ~filters.COMMAND, modify_license_id)],
            MODIFY_LICENSE_FIELD: [MessageHandler(filters.TEXT & ~filters.COMMAND, modify_license_field)],
        },
        fallbacks=[MessageHandler(filters.Regex('^🚫 Cancel$'), start)]
    )
    app.add_handler(conv_handler)
    print("✅ Bot aktif...")
    app.run_polling()

if __name__ == '__main__':
    main()

</code>
        </pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


    
    <!-- API User -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title text-primary">API User</h5>
            <p class="card-text">API ini digunakan untuk validasi lisensi.</p>
            
            <!-- Validasi Lisensi -->
            <div class="mb-4">
                <h6 class="fw-semibold">1. Validasi Lisensi</h6>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge rounded-pill bg-primary me-2">POST</span>
                    <code>/api/check_license.php</code>
                </div>
                
                <p><strong>Parameter:</strong></p>
                <ul class="list-unstyled">
                    <li><span class="badge rounded-pill bg-danger me-2">Required</span> <code>license_key</code>: Key lisensi yang akan divalidasi</li>
                    <li><span class="badge rounded-pill bg-danger me-2">Required</span> <code>device_code</code>: Kode device yang digunakan</li>
                </ul>
                
                <p><strong>Contoh Request:</strong></p>
                <pre class="bg-light p-3 rounded"><code>POST /api/check_license.php HTTP/1.1
Content-Type: application/x-www-form-urlencoded

license_key=abc123&device_code=DEV001</code></pre>
                
                <div class="alert alert-success mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-center rounded-pill bg-success me-2"><i class="ti ti-check"></i></span>
                        <strong>Response (Success):</strong>
                    </div>
                    <pre class="mb-0 mt-2"><code>{
    "status": "success",
    "data": {
        "id": 1,
        "license_key": "abc123",
        "name": "Lisensi 1",
        "expiration_date": "2023-12-31 23:59:59",
        "device_code": "DEV001",
        "is_trial": 0,
        "product_id": 1,
        "product_name": "Dark-FB"
    }
}</code></pre>
                </div>
                
                <div class="alert alert-danger mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-center rounded-pill bg-danger me-2"><i class="ti ti-x"></i></span>
                        <strong>Response (Error):</strong>
                    </div>
                    <pre class="mb-0 mt-2"><code>{
    "status": "error",
    "message": "Device melebihi batas"
}</code></pre>
                </div>
            </div>
        </div>
    </div>
    
    <!-- API Admin -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title text-primary">API Admin</h5>
            <p class="card-text">API ini digunakan untuk manajemen lisensi dan produk. Hanya admin yang memiliki token valid yang dapat mengakses API ini.</p>
            
            <!-- 1. Daftar Lisensi Baru -->
            <div class="mb-4">
                <h6 class="fw-semibold">1. Daftar Lisensi Baru</h6>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge rounded-pill bg-primary me-2">POST</span>
                    <code>/api/add_license.php?token=TOKEN_ADMIN</code>
                </div>
                
                <p><strong>Parameter:</strong></p>
                <ul class="list-unstyled">
                    <li><span class="badge rounded-pill bg-danger me-2">Required</span> <code>name</code>: Nama lisensi</li>
                    <li><span class="badge rounded-pill bg-danger me-2">Required</span> <code>device_code</code>: Kode device</li>
                    <li><span class="badge rounded-pill bg-danger me-2">Required</span> <code>is_trial</code>: 1 = trial, 0 = tidak</li>
                    <li><span class="badge rounded-pill bg-danger me-2">Required</span> <code>duration</code>: Durasi (Bulanan, Mingguan, Harian)</li>
                    <li><span class="badge rounded-pill bg-danger me-2">Required</span> <code>product_id</code>: ID produk terkait</li>
                </ul>
                
                <p><strong>Contoh Request:</strong></p>
                <pre class="bg-light p-3 rounded"><code>POST /api/add_license.php?token=admin_token HTTP/1.1
Content-Type: application/x-www-form-urlencoded

name=Lisensi%201&device_code=DEV001&is_trial=1&duration=Bulanan&product_id=1</code></pre>
                
                <div class="alert alert-success mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-center rounded-pill bg-success me-2"><i class="ti ti-check"></i></span>
                        <strong>Response (Success):</strong>
                    </div>
                    <pre class="mb-0 mt-2"><code>{
    "status": "success",
    "message": "Lisensi berhasil ditambahkan"
}</code></pre>
                </div>
            </div>
            
            <!-- 2. List Lisensi -->
            <div class="mb-4">
                <h6 class="fw-semibold">2. List Lisensi</h6>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge rounded-pill bg-success me-2">GET</span>
                    <code>/api/list_licenses.php?token=TOKEN_ADMIN</code>
                </div>
                
                <p><strong>Contoh Request:</strong></p>
                <pre class="bg-light p-3 rounded"><code>GET /api/list_licenses.php?token=admin_token HTTP/1.1</code></pre>
                
                <div class="alert alert-success mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-center rounded-pill bg-success me-2"><i class="ti ti-check"></i></span>
                        <strong>Response (Success):</strong>
                    </div>
                    <pre class="mb-0 mt-2"><code>{
    "status": "success",
    "data": [
        {
            "id": 1,
            "license_key": "abc123",
            "name": "Lisensi 1",
            "expiration_date": "2023-12-31 23:59:59",
            "device_code": "DEV001",
            "is_trial": 0,
            "product_id": 1
        },
        {
            "id": 2,
            "license_key": "def456",
            "name": "Lisensi 2",
            "expiration_date": "2023-12-31 23:59:59",
            "device_code": "DEV002",
            "is_trial": 1,
            "product_id": 2
        }
    ]
}</code></pre>
                </div>
            </div>
            
            <!-- 3. List Produk -->
            <div class="mb-4">
                <h6 class="fw-semibold">3. List Produk</h6>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge rounded-pill bg-success me-2">GET</span>
                    <code>/api/list_products.php?token=TOKEN_ADMIN</code>
                </div>
                
                <p><strong>Contoh Request:</strong></p>
                <pre class="bg-light p-3 rounded"><code>GET /api/list_products.php?token=admin_token HTTP/1.1</code></pre>
                
                <div class="alert alert-success mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-center rounded-pill bg-success me-2"><i class="ti ti-check"></i></span>
                        <strong>Response (Success):</strong>
                    </div>
                    <pre class="mb-0 mt-2"><code>{
    "status": "success",
    "data": [
        {"id": 1, "name": "Redzone"},
        {"id": 5, "name": "Dark-FB"},
        {"id": 6, "name": "Dark-IG"}
    ],
    "count": 3
}</code></pre>
                </div>
                
                <div class="alert alert-danger mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-center rounded-pill bg-danger me-2"><i class="ti ti-x"></i></span>
                        <strong>Response (Error):</strong>
                    </div>
                    <pre class="mb-0 mt-2"><code>{
    "status": "error",
    "message": "Token tidak valid"
}</code></pre>
                </div>
            </div>
            
            <!-- 4. Modifikasi Lisensi -->
            <div class="mb-4">
                <h6 class="fw-semibold">4. Modifikasi Lisensi</h6>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge rounded-pill bg-primary me-2">POST</span>
                    <code>/api/modify_license.php?token=TOKEN_ADMIN</code>
                </div>
                
                <p><strong>Parameter:</strong></p>
                <ul class="list-unstyled">
                    <li><span class="badge rounded-pill bg-danger me-2">Required</span> <code>id</code>: ID lisensi</li>
                    <li><span class="badge rounded-pill bg-label-primary me-2">Optional</span> <code>name</code>: Nama lisensi</li>
                    <li><span class="badge rounded-pill bg-label-primary me-2">Optional</span> <code>device_code</code>: Kode device</li>
                    <li><span class="badge rounded-pill bg-label-primary me-2">Optional</span> <code>is_trial</code>: 1 = trial, 0 = tidak</li>
                    <li><span class="badge rounded-pill bg-label-primary me-2">Optional</span> <code>expiration_date</code>: Tanggal kedaluwarsa</li>
                    <li><span class="badge rounded-pill bg-label-primary me-2">Optional</span> <code>product_id</code>: ID produk terkait</li>
                </ul>
                
                <p><strong>Contoh Request:</strong></p>
                <pre class="bg-light p-3 rounded"><code>POST /api/modify_license.php?token=admin_token HTTP/1.1
Content-Type: application/x-www-form-urlencoded

id=1&name=Lisensi%201%20Updated&device_code=DEV001&is_trial=0&expiration_date=2024-01-31%2023:59:59&product_id=1</code></pre>
                
                <div class="alert alert-success mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-center rounded-pill bg-success me-2"><i class="ti ti-check"></i></span>
                        <strong>Response (Success):</strong>
                    </div>
                    <pre class="mb-0 mt-2"><code>{
    "status": "success",
    "message": "Lisensi berhasil diupdate"
}</code></pre>
                </div>
            </div>
            
            <!-- 5. Hapus Lisensi -->
            <div class="mb-4">
                <h6 class="fw-semibold">5. Hapus Lisensi</h6>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge rounded-pill bg-primary me-2">POST</span>
                    <code>/api/delete_license.php?token=TOKEN_ADMIN</code>
                </div>
                
                <p><strong>Parameter:</strong></p>
                <ul class="list-unstyled">
                    <li><span class="badge rounded-pill bg-danger me-2">Required</span> <code>id</code>: ID lisensi yang akan dihapus</li>
                </ul>
                
                <p><strong>Contoh Request:</strong></p>
                <pre class="bg-light p-3 rounded"><code>POST /api/delete_license.php?token=admin_token HTTP/1.1
Content-Type: application/x-www-form-urlencoded

id=1</code></pre>
                
                <div class="alert alert-success mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-center rounded-pill bg-success me-2"><i class="ti ti-check"></i></span>
                        <strong>Response (Success):</strong>
                    </div>
                    <pre class="mb-0 mt-2"><code>{
    "status": "success",
    "message": "Lisensi berhasil dihapus"
}</code></pre>
                </div>
            </div>
            
            <!-- 6. Tambah Produk Baru -->
            <div class="mb-4">
                <h6 class="fw-semibold">6. Tambah Produk Baru</h6>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge rounded-pill bg-primary me-2">POST</span>
                    <code>/api/add_product.php?token=TOKEN_ADMIN</code>
                </div>
                
                <p><strong>Parameter:</strong></p>
                <ul class="list-unstyled">
                    <li><span class="badge rounded-pill bg-danger me-2">Required</span> <code>name</code>: Nama produk</li>
                </ul>
                
                <p><strong>Contoh Request:</strong></p>
                <pre class="bg-light p-3 rounded"><code>POST /api/add_product.php?token=admin_token HTTP/1.1
Content-Type: application/x-www-form-urlencoded

name=Produk%20A</code></pre>
                
                <div class="alert alert-success mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-center rounded-pill bg-success me-2"><i class="ti ti-check"></i></span>
                        <strong>Response (Success):</strong>
                    </div>
                    <pre class="mb-0 mt-2"><code>{
    "status": "success",
    "message": "Produk berhasil ditambahkan"
}</code></pre>
                </div>
            </div>
            
            <!-- 7. Hapus Produk -->
            <div class="mb-4">
                <h6 class="fw-semibold">7. Hapus Produk</h6>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge rounded-pill bg-primary me-2">POST</span>
                    <code>/api/delete_product.php?token=TOKEN_ADMIN</code>
                </div>
                
                <p><strong>Parameter:</strong></p>
                <ul class="list-unstyled">
                    <li><span class="badge rounded-pill bg-danger me-2">Required</span> <code>id</code>: ID produk yang akan dihapus</li>
                </ul>
                
                <p><strong>Contoh Request:</strong></p>
                <pre class="bg-light p-3 rounded"><code>POST /api/delete_product.php?token=admin_token HTTP/1.1
Content-Type: application/x-www-form-urlencoded

id=1</code></pre>
                
                <div class="alert alert-success mt-3">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-center rounded-pill bg-success me-2"><i class="ti ti-check"></i></span>
                        <strong>Response (Success):</strong>
                    </div>
                    <pre class="mb-0 mt-2"><code>{
    "status": "success",
    "message": "Produk berhasil dihapus"
}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
<!--/ Content -->

<?php
require '../komponen/footer.php';
?>