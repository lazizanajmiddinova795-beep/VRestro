import paramiko
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=10)
stdin, stdout, stderr = ssh.exec_command('echo "clone2026" | sudo -S chown -R www-data:www-data /home/foodflow/VRestro/storage /home/foodflow/VRestro/bootstrap/cache && echo "clone2026" | sudo -S chmod -R 775 /home/foodflow/VRestro/storage /home/foodflow/VRestro/bootstrap/cache && sudo -u www-data php /home/foodflow/VRestro/artisan optimize:clear && sudo -u www-data php /home/foodflow/VRestro/artisan config:cache && sudo -u www-data php /home/foodflow/VRestro/artisan storage:link')
print(stdout.read().decode('utf-8'))
print(stderr.read().decode('utf-8'))
ssh.close()
