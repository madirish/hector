import gzip
from datetime import datetime
import struct, socket
import logging
import os,sys
import MySQLdb
from __builtin__ import None
from sys import exc_info

appPath = os.path.abspath(os.path.dirname(os.path.realpath(__file__)) + "/../../")
sys.path.append(appPath + "/lib/pylib")

from pull_config import Configurator
configr = Configurator()

# Credentials used for the database connection
configr = Configurator()
DB = configr.get_var('db')
HOST = configr.get_var('db_host')
USERNAME = configr.get_var('db_user')
PASSWORD = configr.get_var('db_pass')


#logging set up
logger = logging.getLogger('NameD Python Script')
hdlr = logging.FileHandler(appPath + '/logs/message_log')
error_hdlr = logging.FileHandler(appPath + '/logs/error_log')
formatter = logging.Formatter('%(asctime)s %(levelname)s %(name)s: %(message)s')
hdlr.setFormatter(formatter)
error_hdlr.setFormatter(formatter)
error_hdlr.setLevel(logging.ERROR)
logger.addHandler(hdlr) 
logger.addHandler(error_hdlr)
logger.setLevel(logging.DEBUG)
logger.debug('args: [\''+('\', \''.join(sys.argv))+'\']')

#config vars
infoblox_dir = configr.get_var('approot')+"app/scripts/infoblox/"
infoblox_log_file_name = "2016-09-26.infoblox.log.gz"
output_filename = ""
blacklisted_ips = set()

current_year = datetime.today().year
current_month = datetime.today().month
domains = {}
uniq_set = set()
count = 0
domain_lookups = 0
domain_inserts = 0
conn = None
cursor = None

filepath = infoblox_dir+'logs/'+infoblox_log_file_name
pathtoarchivefile = infoblox_dir+'logs/2016-09-26.infoblox.csv.gz'
chunkfilepathtemplate = infoblox_dir+'logs/chunks/chunk{0:03d}.csv'
chunksize=10**6

def connect_db():
    global conn,cursor
    logger.info("Opening database connection")
    conn = MySQLdb.connect(host=HOST,
      user=USERNAME,
      passwd=PASSWORD,
      db=DB)
    cursor = conn.cursor()

def convert_date(d):
    dt=datetime.strptime(d,"%b %d %H:%M:%S")
    if current_month<12 and dt.month==12:
        dt=dt.replace(year=current_year-1)
    else:
        dt=dt.replace(year=current_year)
    return dt.strftime("%Y-%m-%d %H:%M:%S")

def is_dns_resolution(l):
    return len(l)>8 and l[5]=='client' and l[8]=='query:'

def is_unique(record):
    global uniq_set
    if record in uniq_set:
        return False
    uniq_set.add(record)
    return True

def get_date(l):
    date = ' '.join(l[:3])
    return convert_date(date)

def get_ip(ip):
    ip = ip.split('#')[0]
    try:
        ip_numeric = struct.unpack('>L',socket.inet_aton(ip))[0]
    except:
        ip_numeric = -1
        logger.error('error with ip numeric for record: {0}'.format(str(l)))
        raise
    
    return ip,ip_numeric

def get_domain_id(domain):
    global domain_lookups
    global domain_inserts
    global domains
    if not domain in domains:
        query = "SELECT domain_id from domain where domain_name=%s"
        
        try:
            cursor.execute(query,(domain,))
        except AttributeError:
            logger.debug('no connection to db. calling connect_db')
            connect_db()
            cursor.execute(query,(domain,))
            
        res = cursor.fetchone()
        domain_lookups+=1
        if res == None:
            cursor.execute("""INSERT INTO domain SET domain_name=%s""",(domain,))
            domains[domain] = int(cursor.lastrowid)
            conn.commit()
            domain_inserts+=1
        else:
            domains[domain] = int(res[0])
    domain_id = domains[domain]
    return domain_id
        
    
#@TODO: get/set src id from db
def get_src_id(l):
    return 1

def proc_line(line):
    l = line.split()
    if is_dns_resolution(l):
        date = get_date(l)
        ip,ip_numeric = get_ip(l[6])
        dm_id = get_domain_id(l[9])
        src_id = get_src_id(l)
        if ip not in blacklisted_ips:
            return ','.join(str(x) for x in [date,ip,ip_numeric,dm_id,src_id])
    return -1

def proc_file(filepath,chunksize):
    logger.debug('processing file: {0}'.format(filepath))
    logger.debug('chunk size: {0}'.format(chunksize))
    count = 0
    fnumber = 0
    fchunkout=None
    chunk =''
    fin = gzip.open(filepath,'r')
    for l in fin:
        res = proc_line(l)
        if res != -1 and is_unique(res):
            chunk+=res+'\n'
            count+=1
            if count % chunksize == 0:
                pathtochunkfile = chunkfilepathtemplate.format(fnumber)
                write_data(chunk,pathtoarchivefile,pathtochunkfile)
                fnumber+=1
                chunk = ''
                
    if chunk!='':
        pathtochunkfile = chunkfilepathtemplate.format(fnumber)
        write_data(chunk,pathtoarchivefile,pathtochunkfile)
        fnumber+=1
    fin.close()              
    logger.info('{0} records written to {1} chunk files'.format(count,fnumber))
    logger.info('{0} domains added.'.format(domain_inserts))
    return fnumber
                
def write_data(data, archivefilepath, chunkfilepath):
    logger.debug('writing to archive file: {0}'.format(archivefilepath))
    archive = gzip.open(archivefilepath,'a')
    archive.write(data)
    archive.close()
    logger.debug('writing to chunk file: {0}'.format(chunkfilepath))
    with open(chunkfilepath,'w') as chunkfile:
        chunkfile.write(data)
    
def import_chunks(chunks):
    query = "load data local infile %s into table named_resolution fields terminated by ',' lines terminated by '\n' " + \
            "(named_resolution_datetime,named_resolution_src_ip,named_resolution_src_ip_numeric,domain_id, named_src_id)"
    for i in chunks:
        logger.info('importing chunk: {0:03d}'.format(i))
        try:
            cursor.execute(query,(chunkfilepathtemplate.format(i),))
            conn.commit()
        except AttributeError:
            logger.debug('no connection to db. calling connect_db')
            connect_db()
            cursor.execute(query,(chunkfilepathtemplate.format(i),))
            conn.commit()
        except:
            logger.error('import chunks error', exc_info=True)
            raise
        logger.info('importing chunk complete.')    

if __name__=='__main__':
    logger.info('named.py starting')
    num_chunks = proc_file(filepath,chunksize)
    import_chunks(xrange(num_chunks))
    
    conn.close()
    logger.info('named.py complete')