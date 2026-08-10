import paramiko
import time

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=10)

shell = ssh.invoke_shell()
shell.send('cd /home/foodflow/VRestro\n')

# 1. Take ownership
shell.send('sudo chown -R foodflow:foodflow /home/foodflow/VRestro\n')
time.sleep(1)
shell.send('clone2026\n')
time.sleep(1)

# 2. Git reset to origin/main
shell.send('git fetch origin main\n')
shell.send('git reset --hard origin/main\n')
time.sleep(4)

# 3. Run npm run build
shell.send('npm run build\n')
time.sleep(25)

# 4. Permissions & cache
shell.send('sudo chown -R www-data:www-data /home/foodflow/VRestro\n')
time.sleep(1)
shell.send('sudo chmod -R 775 /home/foodflow/VRestro/storage /home/foodflow/VRestro/bootstrap/cache /home/foodflow/VRestro/public\n')
time.sleep(1)

shell.send('php artisan config:clear\n')
shell.send('php artisan cache:clear\n')
time.sleep(2)

ssh.close()
print("DEPLOYMENT & SERVER BUILD FULLY COMPLETED!")
