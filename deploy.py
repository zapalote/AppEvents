#!/Users/miguel/.pyenv/shims/python

import json
import os
from ftplib import FTP
import re
import sys
import shutil
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
  try:
    ftp.cwd(dir)
  except:
    return 0
 
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

def generate_js_config(site):
  with open('config_template.js', 'r') as tpl:
    tpl_vars = tpl.read()

  tpl_vars += f"var APP_TITLE = '{site} engagement stats';\n"
  with open("public/config.js", "w") as jscfg:
    jscfg.write(tpl_vars)

def generate_php_config(site):
  with open(f'../{site}/stats/config.php', 'r') as tpl:
    vars = tpl.readlines()

  for v in vars:
    if v.find("STATS_DB_DEV_INI") != -1:
      dev_ini = v
    elif v.find("STATS_DB_PROD_INI") != -1:
      prod_ini = v
    elif v.find("STATS_SITE") != -1:
      stats_site = v

  with open('config_template.php', 'r') as tpl:
    tpl_vars = tpl.readlines()

  with open("public/config.php", "w") as phpcfg:
    for v in tpl_vars:
      if v.find("STATS_DB_DEV_INI") != -1:
        phpcfg.write(dev_ini)
      elif v.find("STATS_DB_PROD_INI") != -1:
        phpcfg.write(prod_ini)
      elif v.find("STATS_SITE") != -1:
        phpcfg.write(stats_site)
      else:
        phpcfg.write(v)


# __main__

if len(sys.argv) == 1:
  print('Usage: deploy.py <site>')
  exit()

# create a fresh build
os.system('yarn build')

# load deployment params
site = sys.argv[1]
with open(f"../{site}/.vscode/sftp.json") as ini:
  cfg = json.load(ini)
app_name = os.path.basename(os.getcwd())
deploy_path = cfg['deploy-'+app_name]

#  generate a JS config file with the site title
generate_js_config(cfg['name'])

# generate a backend php config file with the DB config
generate_php_config(site)

# copy the favicon
shutil.copy(f"../{site}/favicon.ico", "public/favicon.ico")

print(f"Deploying to {cfg['remotePath']+deploy_path}")
last_build = datetime.fromtimestamp(os.stat('build').st_mtime).strftime("%b %d at %T")
print(f"Last build {last_build}")

# connect to site
with FTP(cfg['host'], cfg['username'], cfg['password']) as ftp:
  # ftp.set_debuglevel(1)
  ftp.cwd(cfg['remotePath'])

  # remove previous deployments 
  print("\nCLEAN-UP\n")
  clean_target_dir(deploy_path);
  print('back to ',ftp.pwd())

  # deploy the new build
  print("\nDEPLOY\n")
  deploy_size = 0
  os.chdir('build')
  try:
    ftp.cwd(deploy_path)
  except:
    ftp.mkd(deploy_path)
    ftp.cwd(deploy_path)
  deploy_files('.')
  ftp.quit()

  print(f"\nDEPLOYMENT SIZE: {format_size(deploy_size)}")
