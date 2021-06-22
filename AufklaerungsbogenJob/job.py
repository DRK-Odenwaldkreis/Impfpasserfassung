#!/usr/bin/python3
# coding=utf-8

# This file is part of DRK Testzentrum.

import datetime
from os import path
import logging
from docx import Document 
import subprocess
import codecs
import sys
sys.path.append("..")
from utils.database import Database


logFile = '../../Logs/certificateJob.log'
logging.basicConfig(filename=logFile,level=logging.INFO,
                    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger('Single Certification Job startet on: %s' %(datetime.datetime.now()))
logger.info('Starting single certificate creation Job')
template = "../utils/Aufklaerungsbogen/Template.docx"

if __name__ == "__main__":
    try:
        if len(sys.argv)  != 2:
            logger.debug('Input parameters are not correct, kartenummer needed')
            raise Exception
        else:
            id = sys.argv[1]
            DatabaseConnect = Database()
            sql = "Select Vorname,Nachname,Adresse,Wohnort,Geburtsdatum,Registrierungszeitpunkt from Vorgang where id=%s;"%(id)
            requester = DatabaseConnect.read_single(sql)
            if requester:
                vorname = requester[0]
                nachname = requester[1]
                adresse = requester[2]
                ort = requester[3]
                geburtsdatum = requester[4]
                date = requester[5].strftime("%d.%m.%Y")
                logger.debug('Opening template file')
                inputFile = open(template, 'rb')
                document = Document(inputFile)
                inputFile.close()
                for paragraph in document.paragraphs:
                    paragraph.text = paragraph.text.replace('[[VORNAME]]', str(vorname)).replace('[[NACHNAME]]',str(nachname)).replace('[[GEBDATUM]]',str(geburtsdatum)).replace('[[ADRESSE]]',str(adresse)).replace('[[ORT]]',str(ort)).replace('[[DATE]]',str(date))
                outputFileWord = "../../Zertifikate/" + str(id) + ".docx" 
                document.save(outputFileWord)
                output = subprocess.Popen(['libreoffice', '--convert-to', 'pdf' ,'../../Zertifikate/' + str(id) + ".docx", '--outdir', '../../Zertifikate'])
                print(str(id) + ".pdf")
            else:
                logger.error("id does not exist")
            logger.info('Done')
    except Exception as e:
        logging.error("The following error occured: %s" % (e))
    finally:
        DatabaseConnect.close_connection()
