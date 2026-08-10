import paramiko
import time

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

connected = False
for attempt in range(5):
    try:
        print(f"Connecting attempt {attempt+1}...")
        ssh.connect('100.65.139.1', username='foodflow', password='clone2026', timeout=10)
        connected = True
        print("Connected!")
        break
    except Exception as e:
        print(f"Attempt {attempt+1} failed: {e}")
        time.sleep(3)

if connected:
    shell = ssh.invoke_shell()
    shell.send('cd /home/foodflow/VRestro\n')
    shell.send('git pull origin main\n')
    time.sleep(3)
    shell.send('php artisan migrate --force\n')
    time.sleep(3)
    out1 = shell.recv(9999).decode('utf-8')
    print(out1)
    ssh.close()
