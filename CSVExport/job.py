#!/usr/bin/python3
# coding=utf-8

# This file is part of Impfpasserfassung.


import logging
import locale
import time
import datetime
import sys
sys.path.append("..")
from utils.database import Database
from createCSV import create_CSV
from utils.sendmail import send_csv_report
import pyexcel

logFile = '../../Logs/CSVExportJob.log'
logging.basicConfig(filename=logFile,level=logging.INFO,
                    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger('CSV Export')
logger.info('Starting')


if __name__ == "__main__":
    try:
        DatabaseConnect = Database()
        if len(sys.argv) == 2:
            requestedDate = sys.argv[1]
            sql = "Select Vorgang.id,Vorgang.Nachname,Vorgang.Vorname,Vorgang.Geburtsdatum,Vorgang.Wohnort,Vorgang.Adresse,Vorgang.Mailadresse,Vorgang.Teststation,Vorgang.Registrierungszeitpunkt,Vorgang.Erstimpfung,Vorgang.Zweitimpfung,Erstimpfstoff.Name AS Erstimpfstoff,Zweitimpfstoff.Name as Zweitimpfstoff from Vorgang LEFT JOIN Impfstoff AS Erstimpfstoff ON Erstimpfstoff_id=Erstimpfstoff.id LEFT JOIN Impfstoff AS Zweitimpfstoff ON Zweitimpfstoff_id=Zweitimpfstoff.id where Registrierungszeitpunkt Between '%s 00:00:00' and '%s 23:59:59';" % (
            requestedDate.replace('-', '.'), requestedDate.replace('-', '.'))
        elif len(sys.argv) == 3:
            requestedDate = sys.argv[1]
            sql = "SELECT Station FROM li_user WHERE id=%s" % (sys.argv[2])
            station = DatabaseConnect.read_single(sql)[0]
            if not station:
                station = 0
            sql = "Select Vorgang.id,Vorgang.Nachname,Vorgang.Vorname,Vorgang.Geburtsdatum,Vorgang.Wohnort,Vorgang.Adresse,Vorgang.Mailadresse,Vorgang.Teststation,Vorgang.Registrierungszeitpunkt,Vorgang.Erstimpfung,Vorgang.Zweitimpfung,Erstimpfstoff.Name AS Erstimpfstoff,Zweitimpfstoff.Name as Zweitimpfstoff from Vorgang LEFT JOIN Impfstoff AS Erstimpfstoff ON Erstimpfstoff_id=Erstimpfstoff.id LEFT JOIN Impfstoff AS Zweitimpfstoff ON Zweitimpfstoff_id=Zweitimpfstoff.id where Teststation = %s Registrierungszeitpunkt Between '%s 00:00:00' and '%s 23:59:59';" % (station,requestedDate.replace('-', '.'), requestedDate.replace('-', '.'))
        else:
            logger.debug(
                'Input parameters are not correct, date needed')
            raise Exception
        logger.debug(
            'Getting all Events for employee of the month and year with the following query: %s' % (sql))
        exportEvents = DatabaseConnect.read_all(sql)
        logger.debug('Received the following entries: %s' %
                     (str(exportEvents)))
        filename = create_CSV(exportEvents, requestedDate)
        sheet = pyexcel.get_sheet(file_name=filename, delimiter=";")
        sheet.save_as(str(filename).replace('csv','xlsx')) 
        print(filename.replace('csv','xlsx').replace('../../Reports/', ''))
        logger.info('Done')
    except Exception as e:
        logging.error("The following error occured: %s" % (e))
    finally:
        DatabaseConnect.close_connection()
