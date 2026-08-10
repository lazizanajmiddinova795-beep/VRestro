import paramiko
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=10)
stdin, stdout, stderr = ssh.exec_command('tail -n 150 /home/foodflow/VRestro/storage/logs/laravel.log | grep -v "TelegramAPIError" | grep -v "OrderDispatched" | grep -v "NotificationBroadcast" | grep -v "delete from \\"cache\\""')
print(stdout.read().decode('utf-8'))
ssh.close()
