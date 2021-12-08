#!/Users/miguel/.pyenv/shims/python

import json
import os
from ftplib import FTP
import re
import sys
from datetime import datetime
from stat import *
from humanfriendly import format_size

def printRed(txt):
	return f"\x1B[31m{txt}\x1B[0m"

def printGreen(txt):
	return f"\x1B[32m{txt}\x1B[0m"

def clean_target_dir(dir):
  global ftp

  print(f"cleaning {dir}")
	# List files on remote
  oldremote = ftp.pwd()
  ftp.cwd(dir) 
  listing = []
  ftp.dir(listing.append)

  for line in listing:
    l = line.split()
    file = l[8]
    fdir = (l[0].find("d") == 0)
    if fdir:
      if file == "." or file == ".." : continue
      clean_target_dir(file)
      continue

    print(f"Deleting {file} in {dir}")
    ftp.delete(file)
    # end loop lines in listing

  ftp.cwd(oldremote)
  # end clean _target_dir

def deploy_files(dir):
  global ftp
  global deploy_size

  oldpath = os.getcwd()
  try:
    os.chdir(dir)
  except FileNotFoundError:
    return 0

	# List files on remote
  oldremote = ftp.pwd()
  try:
    ftp.cwd(dir)
  except:
    ftp.mkd(dir)
    ftp.cwd(dir) 

  listing = os.listdir('.')
  for file in listing:
    if S_ISDIR(os.stat(file).st_mode):
      if file == "." or file == ".." : continue
      deploy_files(file)
      continue

    deploy_size += int(os.stat(file).st_size)
    print(f'uploading {file} to {dir}, deploy size: {deploy_size}')
    ftp.storbinary(f"STOR {file}", open(file, 'rb'))
    #end loop lines in listing

  os.chdir(oldpath)
  ftp.cwd(oldremote)
  # end deploy_files

# __main__

os.system('yarn build')

with open("../.vscode/sftp.json") as ini:
  cfg = json.load(ini)

app_name = os.path.basename(os.getcwd())
deploy_path = cfg['deploy-'+app_name]
print(f"Deploying to {cfg['remotePath']+deploy_path}")
last_build = datetime.fromtimestamp(os.stat('build').st_mtime).strftime("%b %d at %T")
print(f"Last build {last_build}")

with FTP(cfg['host'], cfg['username'], cfg['password']) as ftp:
  # ftp.set_debuglevel(1)
  ftp.cwd(cfg['remotePath'])

  print("\nCLEAN-UP\n")
  clean_target_dir(deploy_path);
  print(ftp.pwd())

  print("\nDEPLOY\n")
  deploy_size = 0
  os.chdir('build')
  ftp.cwd(deploy_path)
  deploy_files('.')

  print(f"\nDEPLOYMENT SIZE: {format_size(deploy_size)}")
