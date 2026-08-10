import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=15)

cmd = '''
cd /home/foodflow/VRestro
echo clone2026 | sudo -S chown -R foodflow:foodflow /home/foodflow/VRestro
git fetch origin main
git reset --hard origin/main
npm run build
echo clone2026 | sudo -S chown -R www-data:www-data /home/foodflow/VRestro
echo clone2026 | sudo -S chmod -R 775 /home/foodflow/VRestro/storage /home/foodflow/VRestro/bootstrap/cache /home/foodflow/VRestro/public
php artisan config:clear
php artisan cache:clear
'''

stdin, stdout, stderr = ssh.exec_command(cmd, get_pty=True)
out = stdout.read().decode('utf-8', errors='ignore')
print("REMOTE COMMAND OUTPUT:")
print(out)
ssh.close()
print("SERVER DEPLOYMENT FULLY DONE!")
