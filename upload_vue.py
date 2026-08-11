import paramiko
import os
import time

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026')

print("Connected. Uploading Vue files...")
sftp = ssh.open_sftp()
sftp.put(r'D:\loyhalar\VRestro\resources\js\components\CashierOrder.vue', '/home/foodflow/VRestro/resources/js/components/CashierOrder.vue')
sftp.put(r'D:\loyhalar\VRestro\resources\js\components\ReceiptPreview.vue', '/home/foodflow/VRestro/resources/js/components/ReceiptPreview.vue')
sftp.put(r'D:\loyhalar\VRestro\app\Services\PaymentService.php', '/home/foodflow/VRestro/app/Services/PaymentService.php')
sftp.put(r'D:\loyhalar\VRestro\app\Services\OrderService.php', '/home/foodflow/VRestro/app/Services/OrderService.php')
sftp.put(r'D:\loyhalar\VRestro\resources\css\app.css', '/home/foodflow/VRestro/resources/css/app.css')
sftp.put(r'D:\loyhalar\VRestro\resources\js\stores\settings.js', '/home/foodflow/VRestro/resources/js/stores/settings.js')
sftp.put(r'D:\loyhalar\VRestro\resources\js\components\SettingsManagement.vue', '/home/foodflow/VRestro/resources/js/components/SettingsManagement.vue')
sftp.put(r'D:\loyhalar\VRestro\resources\js\stores\cashier.js', '/home/foodflow/VRestro/resources/js/stores/cashier.js')
sftp.put(r'D:\loyhalar\VRestro\resources\js\components\CashierTables.vue', '/home/foodflow/VRestro/resources/js/components/CashierTables.vue')
sftp.put(r'D:\loyhalar\VRestro\resources\js\components\StaffManagement.vue', '/home/foodflow/VRestro/resources/js/components/StaffManagement.vue')
sftp.put(r'D:\loyhalar\VRestro\app\Services\StaffService.php', '/home/foodflow/VRestro/app/Services/StaffService.php')
sftp.close()

print("Uploaded. Running build...")
stdin, stdout, stderr = ssh.exec_command('cd /home/foodflow/VRestro && npm run build')
exit_status = stdout.channel.recv_exit_status()
print(stdout.read().decode('utf-8', errors='ignore').encode('cp1251', errors='replace').decode('cp1251'))
print(stderr.read().decode('utf-8'))

ssh.close()
print("Build finished with status:", exit_status)
