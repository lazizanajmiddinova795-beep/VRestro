import paramiko
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect('192.168.1.13', username='foodflow', password='clone2026', timeout=5)
    print("Connected via LAN 192.168.1.13")
    ssh.close()
except Exception as e:
    print("LAN failed:", e)
