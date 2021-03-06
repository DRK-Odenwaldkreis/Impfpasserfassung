#!/usr/bin/python3
# coding=utf-8

# This file is part of Impfpasserfassung.

import datetime
import locale
from os import path
import logging
import sys
sys.path.append("..")
from utils.database import Database
from utils.sendmail import send_linked_certificates


logFile = '../../Logs/rotationJob.log'
logging.basicConfig(filename=logFile,level=logging.INFO,
                    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger('Rendition Job startet on: %s' %(datetime.datetime.now()))
logger.info('Starting Rendition Job')

if __name__ == "__main__":
    try:
        if len(sys.argv) == 2:
            logger.debug('Input parameters are with station id')
            stationID = sys.argv[1]
            sql = "Select Vorname, Nachname, Mailadresse, Registrierungszeitpunkt,id,customer_key,privateMail_lock from Vorgang where privateMail_request=1 and ((privateMail_lock < 10 and privateMail_lock != 0) or privateMail_lock is NULL) and Teststation = %s;" % (stationID)
        else:
            logger.debug('Checking all stations')
            sql = "Select Vorname, Nachname, Mailadresse, Registrierungszeitpunkt,id,customer_key,privateMail_lock from Vorgang where privateMail_request=1 and ((privateMail_lock < 10 and privateMail_lock != 0) or privateMail_lock is NULL);"
        DatabaseConnect = Database()
        logger.debug('Checking for new results, using the following query: %s' % (sql))
        content = DatabaseConnect.read_all(sql)
        logger.debug(
            'Received the following content: %s' % (content))
        if len(content) > 0:
            logger.debug('Content contains infos')
            for i in content:
                try:
                    vorname = i[0]
                    nachname = i[1]
                    mail = i[2]
                    date = i[3].strftime("%d.%m.%Y um %H:%M Uhr")
                    id = i[4]
                    customer_key = i[5]
                    mail_lock = i[6]
                    if mail_lock is None:
                        mail_lock=0
                    link = "https://www.impfpass-odw.de/result.php?t=%s&i=%s" %(customer_key,id)
                    transmission = send_linked_certificates(vorname,nachname,mail,date,link)
                    logger.debug('Checking whether mail was send properly and closing db entry')
                    if transmission:
                        logger.debug('Mail was succesfully send, closing entry in db')
                        sql = "Update Vorgang SET privateMail_lock=0 WHERE id = %s;" % (id)
                        DatabaseConnect.update(sql)
                    else:
                        mail_lock +=1
                        sql = "Update Vorgang SET privateMail_lock = %s WHERE id = %s;" % (mail_lock,id)
                        DatabaseConnect.update(sql)
                except Exception as e:
                    logging.error("The following error occured in loop of content: %s" % (e))
        else:
            logger.debug('Nothing to do')
        logger.info('Done')
    except Exception as e:
        logging.error("The following error occured: %s" % (e))
    finally:
        DatabaseConnect.close_connection()
