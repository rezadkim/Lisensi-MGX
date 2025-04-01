import requests
from telegram import Update, ReplyKeyboardMarkup, ReplyKeyboardRemove
from telegram.ext import (
    ApplicationBuilder, CommandHandler, MessageHandler,
    ConversationHandler, ContextTypes, filters
)
from datetime import datetime

# Konfigurasi bot
TOKEN = 'tokenbottelegram'
BASE_URL = 'http://localhost/lisensimanager/api'
ADMIN_TOKEN = 'token dari mgx admin'
ADMIN_ID = 6994514050 #userid telegram kamu agar dapat mengakses bot

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
