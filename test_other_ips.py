import paramiko
for ip in ['100.92.238.113', '100.69.189.125']:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        ssh.connect(ip, username='foodflow', password='clone2026', timeout=3)
        print(f"Connected to {ip}")
        ssh.close()
    except Exception as e:
        print(f"{ip} failed:", e)
