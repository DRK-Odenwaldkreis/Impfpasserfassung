# This file is part of Impfpasserfassung.


import pyqrcode
import png
import sys
sys.path.append("..")
from utils.database import Database
from pdfcreator.pdf import PDFgenerator
from utils.sendmail import send_qr_ticket_mail
from utils.slot import get_slot_time
import datetime
import time
import locale
import logging


logFile = '../../Logs/ticketGeneration.log'
logging.basicConfig(level=logging.DEBUG,
                    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger('Generating Tickets')
logger.info('Starting Ticketgeneration')


if __name__ == "__main__":
    try:
        DatabaseConnect = Database()
        sql = "Select Voranmeldung.Vorname, Voranmeldung.Nachname, Voranmeldung.Mailadresse, Termine.Slot, Termine.Stunde, Voranmeldung.Tag, Voranmeldung.Token, Voranmeldung.id, Station.Ort, Station.Adresse from Voranmeldung JOIN Termine ON Termine.id=Voranmeldung.Termin_id JOIN Station ON Termine.id_station=Station.id where Voranmeldung.Token is not NULL and Voranmeldung.Mailsend = 0 and Termine.Slot is not  NULL;"
        content = DatabaseConnect.read_all(sql)
        logger.debug('Received the following recipients: %s' %(str(content)))
        for i in content:
            try:
                logger.debug('Received the following entry: %s' %(str(i)))
                vorname = i[0]
                nachname = i[1]
                mail = i[2]
                slot = i[3]
                stunde = i[4]
                date = i[5]
                token = i[6]
                entry = i[7]
                ort = i[8]
                adress = i[9]
                appointment = get_slot_time(slot,stunde)
                location = str(ort) + ", " + str(adress)
                PDF = PDFgenerator()
                filename = PDF.creatPDF(i,location)
                url = "https://impfpass-odw.de/registration/index.php?cancel=cancel&t=%s&i=%s" % (token,entry)
                if send_qr_ticket_mail(mail,date,vorname,nachname,appointment,location,filename,url): 
                    logger.debug('Mail was succesfully send, closing entry in db')
                    sql = "Update Voranmeldung SET Mailsend = 1 WHERE id = %s;" % (entry)
                    DatabaseConnect.update(sql)
            except Exception as e:
                logging.error("The following error occured in loop of content: %s" % (e))
        logger.info("Done")
    except Exception as e:
        logging.error("The following error occured: %s" % (e))
    finally:
        DatabaseConnect.close_connection()
