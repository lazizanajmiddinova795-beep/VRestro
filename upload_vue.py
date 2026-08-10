import paramiko
import os
import time
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026')

sftp = ssh.open_sftp()
sftp.put(r'D:\loyhalar\VRestro\resources\js\components\CashierOrder.vue', '/home/foodflow/VRestro/resources/js/components/CashierOrder.vue')
sftp.put(r'D:\loyhalar\VRestro\resources\js\components\ReceiptPreview.vue', '/home/foodflow/VRestro/resources/js/components/ReceiptPreview.vue')
sftp.close()

shell = ssh.invoke_shell()
shell.send('cd /home/foodflow/VRestro\n')
shell.send('npm run build\n')
time.sleep(2)
out = shell.recv(9999).decode('utf-8')
print(out)
ssh.close()
