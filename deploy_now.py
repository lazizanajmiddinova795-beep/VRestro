import paramiko
import os
import time

def put_dir(sftp, local_dir, remote_dir):
    try:
        sftp.mkdir(remote_dir)
    except IOError:
        pass
    for item in os.listdir(local_dir):
        local_path = os.path.join(local_dir, item)
        remote_path = remote_dir + '/' + item
        if os.path.isfile(local_path):
            sftp.put(local_path, remote_path)
        else:
            put_dir(sftp, local_path, remote_path)

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=10)

shell = ssh.invoke_shell()
shell.send('cd /home/foodflow/VRestro\n')
shell.send('git pull origin main\n')
time.sleep(3)
shell.send('php artisan migrate --force\n')
time.sleep(3)
shell.send('php artisan config:clear\n')
time.sleep(1)
shell.send('php artisan cache:clear\n')
time.sleep(1)
out1 = shell.recv(9999).decode('utf-8')
print("PULL & MIGRATE OUTPUT:")
print(out1)

print("Uploading public/build assets...")
sftp = ssh.open_sftp()
put_dir(sftp, r'D:\loyhalar\VRestro\public\build', '/tmp/build')
sftp.close()

shell.send('sudo rm -rf /home/foodflow/VRestro/public/build\n')
time.sleep(1)
shell.send('clone2026\n')
time.sleep(1)
shell.send('sudo mv /tmp/build /home/foodflow/VRestro/public/build\n')
time.sleep(1)
shell.send('sudo chown -R www-data:www-data /home/foodflow/VRestro/public/build /home/foodflow/VRestro/storage /home/foodflow/VRestro/bootstrap/cache\n')
time.sleep(1)
shell.send('sudo chmod -R 775 /home/foodflow/VRestro/public/build /home/foodflow/VRestro/storage /home/foodflow/VRestro/bootstrap/cache\n')
time.sleep(1)
out2 = shell.recv(9999).decode('utf-8')
print("BUILD & PERMISSION OUTPUT:")
print(out2)

ssh.close()
print("SERVER DEPLOYMENT FULLY COMPLETED SUCCESSFUL!")
