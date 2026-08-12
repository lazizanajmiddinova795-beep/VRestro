import { Client } from 'ssh2';

const conn = new Client();
conn.on('ready', () => {
  console.log('Client :: ready');
  conn.exec('ls -la && pwd && find . -maxdepth 2 -type d -name VRestro', (err, stream) => {
    if (err) throw err;
    stream.on('close', (code, signal) => {
      console.log('Stream :: close :: code: ' + code + ', signal: ' + signal);
      conn.end();
    }).on('data', (data) => {
      console.log('STDOUT: ' + data);
    }).stderr.on('data', (data) => {
      console.log('STDERR: ' + data);
    });
  });
}).connect({
  host: '192.168.1.3', // server IP from prompt
  port: 22,
  username: 'foodflow',
  password: 'clone2026',
  readyTimeout: 10000
});
