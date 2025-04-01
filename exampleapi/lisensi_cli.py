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
ADMIN_TOKEN = "token dari mgx admin"

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
