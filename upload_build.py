import paramiko
import os

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
ssh.connect('100.65.139.1', username='foodflow', password='clone2026')
sftp = ssh.open_sftp()
put_dir(sftp, r'D:\loyhalar\VRestro\public\build', '/home/foodflow/VRestro/public/build')
sftp.close()
ssh.close()
print("Upload completed")
