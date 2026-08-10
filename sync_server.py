import paramiko
import time

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=10)

shell = ssh.invoke_shell()
shell.send('cd /home/foodflow/VRestro\n')
shell.send('git init\n')
shell.send('git remote add origin https://github.com/lazizanajmiddinova795-beep/VRestro.git || git remote set-url origin https://github.com/lazizanajmiddinova795-beep/VRestro.git\n')
shell.send('git fetch origin main\n')
shell.send('git reset --hard origin/main\n')
time.sleep(5)

shell.send('php artisan migrate --force\n')
time.sleep(3)

shell.send('sudo chown -R www-data:www-data /home/foodflow/VRestro/storage /home/foodflow/VRestro/bootstrap/cache /home/foodflow/VRestro/public\n')
time.sleep(1)
shell.send('clone2026\n')
time.sleep(1)
shell.send('sudo chmod -R 775 /home/foodflow/VRestro/storage /home/foodflow/VRestro/bootstrap/cache /home/foodflow/VRestro/public\n')
time.sleep(1)

shell.send('php artisan config:clear\n')
shell.send('php artisan cache:clear\n')
time.sleep(2)

out = shell.recv(9999).decode('utf-8')
print("SYNC OUTPUT:")
print(out)
ssh.close()
print("DEPLOYMENT DONE!")
