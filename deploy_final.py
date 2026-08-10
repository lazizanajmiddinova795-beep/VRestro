import paramiko

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=15)

cmd = '''
git config --global --add safe.directory /home/foodflow/VRestro
echo clone2026 | sudo -S chown -R foodflow:foodflow /home/foodflow/VRestro
cd /home/foodflow/VRestro
git fetch origin main
git reset --hard origin/main
npm run build
echo clone2026 | sudo -S chown -R www-data:www-data /home/foodflow/VRestro
echo clone2026 | sudo -S chmod -R 775 /home/foodflow/VRestro/storage /home/foodflow/VRestro/bootstrap/cache /home/foodflow/VRestro/public
php artisan config:clear
php artisan cache:clear
git log -n 1 --oneline
'''

stdin, stdout, stderr = ssh.exec_command(cmd, get_pty=True)
out = stdout.read().decode('utf-8', errors='ignore')

# Write output safely to text file to avoid cp1251 print crash
with open('D:\\loyhalar\\VRestro\\deploy_log.txt', 'w', encoding='utf-8') as f:
    f.write(out)

ssh.close()
print("SERVER DEPLOYMENT LOG SAVED TO deploy_log.txt!")
