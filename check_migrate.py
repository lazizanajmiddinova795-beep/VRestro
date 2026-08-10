import paramiko
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=10)
shell = ssh.invoke_shell()
shell.send('cd /home/foodflow/VRestro && php artisan migrate:status\n')
import time
time.sleep(2)
out = shell.recv(9999).decode('utf-8')
print(out)
ssh.close()
