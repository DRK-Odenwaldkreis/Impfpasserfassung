#!/usr/bin/python3
# coding=utf-8

# This file is part of Impfpasserfassung.
from zipfile import ZipFile

import smtplib
import datetime
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.base import MIMEBase
from email import encoders
from email.message import EmailMessage
import logging
import sys
import os
sys.path.append("..")

from utils.readconfig import read_config
from utils.month import monthInt_to_string

logger = logging.getLogger('Send Mail')
logger.debug('Starting')

FROM_EMAIL = read_config("Mail", "FROM_EMAIL")
TO_EMAIL = read_config("Mail", "TO_EMAIL")
SMTP_SERVER = read_config("Mail", "SMTP_SERVER")
SMTP_USERNAME = read_config("Mail", "SMTP_USERNAME")
SMTP_PASSWORD = read_config("Mail", "SMTP_PASSWORD")
simulationMode = 0

def send_mail_report(filenames, day, recipients):
    try:
        logging.debug(
            "Receviced the following filename %s to be sent." % (filenames))
        message = MIMEMultipart()
        with open('../utils/MailLayout/NewReport.html', encoding='utf-8') as f:
            fileContent = f.read()
        messageContent = fileContent.replace('[[DAY]]', str(day))
        message.attach(MIMEText(messageContent, 'html'))
        message['Subject'] = "Neuer Tagesreport für: %s" % (str(day))
        message['From'] = FROM_EMAIL
        message['Reply-To'] = FROM_EMAIL
        message['Cc'] = 'testzentrum@drk-odenwaldkreis.de, info@impfpass-odw.de'
        message['To'] = ", ".join(recipients)
        files = [filenames]
        for item in files:
            attachment = open(item, 'rb')
            part = MIMEBase('application', 'octet-stream')
            part.set_payload((attachment).read())
            encoders.encode_base64(part)
            part.add_header(
                'Content-Disposition', "attachment; filename= " + item.replace('../../Reports/', ''))
            message.attach(part)
        logging.debug("Starting SMTP Connection")
        smtp = smtplib.SMTP(SMTP_SERVER, port=587)
        smtp.starttls()
        smtp.login(SMTP_USERNAME, SMTP_PASSWORD)
        if simulationMode == 0:
            logging.debug("Going to send message")
            smtp.send_message(message)
            logging.debug("Mail was send")
        smtp.quit()
        return True
    except Exception as err:
        logging.error(
            "The following error occured in send mail report: %s" % (err))
        return False


def send_csv_report(filename, day):
    try:
        logging.debug("Receviced the following filename %s to be sent." % (filename))
        message = MIMEMultipart()
        url = 'https://impfpass-odw.de/download.php?file=' + str(filename)
        with open('../utils/MailLayout/NewCSVReport.html', encoding='utf-8') as f:
            fileContent = f.read()
        messageContent = fileContent.replace(
            '[[DAY]]', str(day)).replace('[[LINK]]', str(url))
        message.attach(MIMEText(messageContent, 'html'))
        recipients = ['','','']
        message['Subject'] = "Neuer CSV Export für: %s" % (str(day))
        message['From'] = FROM_EMAIL
        message['Reply-To'] = FROM_EMAIL
        message['To'] = ", ".join(recipients)
        logging.debug("Starting SMTP Connection")
        smtp = smtplib.SMTP(SMTP_SERVER,port=587)
        smtp.starttls()
        smtp.login(SMTP_USERNAME, SMTP_PASSWORD)
        if simulationMode == 0:
            logging.debug("Going to send message")
            smtp.send_message(message)
            logging.debug("Mail was send")
        smtp.quit()
        return True
    except Exception as err:
        logging.error(
            "The following error occured in send csv report mail: %s" % (err))
        return False


def send_cancel_appointment(recipient, date, vorname, nachname):
    try:
        logging.debug("Receviced the following recipient: %s to be sent to." % (
            recipient))
        message = MIMEMultipart()
        with open('../utils/MailLayout/Cancelation.html', encoding='utf-8') as f:
            fileContent = f.read()
        messageContent = fileContent.replace('[[DATE]]', date.strftime("%d.%m.%Y")).replace('[[VORNAME]]', str(vorname)).replace('[[NACHNAME]]', str(nachname))
        message.attach(MIMEText(messageContent, 'html'))
        message['Subject'] = "Ihr Termin im Testzentrum des Odenwaldkreis am %s wurde storniert" % (str(date))
        message['From'] = "Testzentrum Odenwaldkreis" + f' <{FROM_EMAIL}>'
        message['Reply-To'] = FROM_EMAIL
        message['To'] = recipient
        smtp = smtplib.SMTP(SMTP_SERVER, port=587)
        smtp.starttls()
        smtp.login(SMTP_USERNAME, SMTP_PASSWORD)
        if simulationMode == 0:
            logging.debug("Going to send message")
            smtp.send_message(message)
            logging.debug("Mail was send")
        smtp.quit()
        return True
    except Exception as err:
        logging.error(
            "The following error occured in send mail reminder: %s" % (err))
        return False

def send_linked_certificate(vorname, nachname, mail, date, link):
    try:
        logging.debug("Receviced the following recipient %s" % (mail))
        message = EmailMessage()
        text = "Hallo %s %s, \nSie waren am %s im Testzentrum und haben sich Ihren Impfpass digitalisieren lassen. Die Codes sind zum Abruf bereit. \nDiese können zusammen mit Ihrem Geburtsdatum über den folgenden Link abgerufen werden: \n \n%s \n \nBitte beachten Sie: Der Link ist individuell nur für die Person in der Ansprache. Sofern Sie für mehrere \nPersonen die gleiche Mailadresse eingegeben haben bekommen Sie individuelle Mails für jede Person. \nViele Grüße \nTestteam Odenwaldkreis \n\n\n----------------ENGLISH------------\nYou were at one of our testing centers. \nYour result can be received by following the link above together with your date of birth." %(vorname,nachname,date,link)
        message.set_content(text)
        message['Subject'] = "Impfpasszertifikate können heruntergeladen werden"
        message['From'] = "Testzentrum Odenwaldkreis" + f' <{FROM_EMAIL}>'
        message['Reply-To'] = FROM_EMAIL
        message['To'] = mail
        logging.debug("Starting SMTP Connection")
        smtp = smtplib.SMTP(SMTP_SERVER, port=587)
        smtp.starttls()
        smtp.login(SMTP_USERNAME, SMTP_PASSWORD)
        if simulationMode == 0:
            logging.debug("Going to send message")
            smtp.send_message(message)
            logging.debug("Mail was send")
        smtp.quit()
        return True
    except Exception as err:
        logging.error(
            "The following error occured in send negative mail: %s" % (err))
        return False

def send_mail_reminder(recipient, date, vorname, nachname, appointment, ort, filename, url):
    try:
        logging.debug("Receviced the following recipient: %s to be sent to." % (
            recipient))
        message = MIMEMultipart()
        with open('../utils/MailLayout/Reminder.html', encoding='utf-8') as f:
            fileContent = f.read()
        messageContent = fileContent.replace('[[DATE]]', date.strftime("%d.%m.%Y")).replace('[[VORNAME]]', str(vorname)).replace('[[NACHNAME]]', str(nachname)).replace('[[SLOT]]', str(appointment)).replace('[[LINK]]', str(url)).replace('[[ORT]]', str(ort))
        message.attach(MIMEText(messageContent, 'html'))
        message['Subject'] = "Erinnerung an Termin %s im Testzentrum des Odenwaldkreis am %s zur Digitalisierung des Impfnachweises" % (str(appointment), str(date))
        message['From'] = "Testzentrum Odenwaldkreis" + f' <{FROM_EMAIL}>'
        message['Reply-To'] = FROM_EMAIL
        message['To'] = recipient
        files = [filename]
        for item in files:
            attachment = open(item, 'rb')
            part = MIMEBase('application', 'octet-stream')
            part.set_payload((attachment).read())
            encoders.encode_base64(part)
            part.add_header(
                'Content-Disposition', "attachment; filename= " + item.replace('../../Tickets/', ''))
            message.attach(part)
        smtp = smtplib.SMTP(SMTP_SERVER, port=587)
        smtp.starttls()
        smtp.login(SMTP_USERNAME, SMTP_PASSWORD)
        if simulationMode == 0:
            logging.debug("Going to send message")
            smtp.send_message(message)
            logging.debug("Mail was send")
        smtp.quit()
        return True
    except Exception as err:
        logging.error(
            "The following error occured in send mail reminder: %s" % (err))
        return False

def send_qr_ticket_mail(recipient, date, vorname, nachname, appointment, ort, filename, url):
    try:
        logging.debug("Receviced the following recipient: %s to be sent to." % (
            recipient))
        message = MIMEMultipart()
        with open('../utils/MailLayout/QRTicket.html', encoding='utf-8') as f:
            fileContent = f.read()
        messageContent = fileContent.replace('[[DATE]]', date.strftime("%d.%m.%Y")).replace('[[VORNAME]]', str(vorname)).replace('[[NACHNAME]]', str(nachname)).replace('[[SLOT]]', str(appointment)).replace('[[LINK]]', str(url)).replace('[[ORT]]', str(ort))
        message['Subject'] = "Persönlicher Termin um %s im Testzentrum Odenwaldkreis am %s zur Digitalisierung des Impfnachweises" % (str(appointment), str(date))
        message.attach(MIMEText(messageContent, 'html'))
        message['From'] = "Testzentrum Odenwaldkreis" + f' <{FROM_EMAIL}>'
        message['Reply-To'] = FROM_EMAIL
        message['To'] = recipient
        files = [filename]
        for item in files:
            attachment = open(item, 'rb')
            part = MIMEBase('application', 'octet-stream')
            part.set_payload((attachment).read())
            encoders.encode_base64(part)
            part.add_header(
                'Content-Disposition', "attachment; filename= " + item.replace('../../Tickets/', ''))
            message.attach(part)
        smtp = smtplib.SMTP(SMTP_SERVER, port=587)
        smtp.starttls()
        smtp.login(SMTP_USERNAME, SMTP_PASSWORD)
        if simulationMode == 0:
            logging.debug("Going to send message")
            smtp.send_message(message)
            logging.debug("Mail was send")
        smtp.quit()
        return True
    except Exception as err:
        logging.error(
            "The following error occured in send mail qr ticket: %s" % (err))
        return False


def send_mail_download_sheet(filename, requester):
    try:
        logging.debug("Receviced the following filename %s to be sent to %s" % (filename, requester))
        message = MIMEMultipart()
        url = 'https://www.impfpass-odw.de/zentral/download.php?dir=ls&file=' + str(filename)
        logging.debug("The created url is %s" % (url))
        with open('../utils/MailLayout/NewDownload.html', encoding='utf-8') as f:
            fileContent = f.read()
        messageContent = fileContent.replace('[[LINK]]', str(url))
        message.attach(MIMEText(messageContent, 'html'))        
        message['Subject'] = "Neuer Download verfügbar"
        message['From'] = "Testzentrum Odenwaldkreis" + f' <{FROM_EMAIL}>'
        message['To'] = requester
        smtp = smtplib.SMTP(SMTP_SERVER,port=587)
        smtp.starttls()
        smtp.login(SMTP_USERNAME, SMTP_PASSWORD)
        logging.debug(
            "Sending Mail with following tupel: %s" % (message))
        smtp.send_message(message)
        logging.debug("Mail was send")
        smtp.quit()
        return True
    except Exception as err:
        logging.error("The following error occured in send mail download: %s" % (err))
        return False

def send_mail_download_certificate(filename, token, requester):
    try:
        logging.debug("Receviced the following filename %s to be sent to %s" % (filename, requester))
        message = MIMEMultipart()
        url = 'https://www.impfpass-odw.de/zentral/download.php?dir=zip&t=%s&file=%s' %(token,filename)
        logging.debug("The created url is %s" % (url))
        with open('../utils/MailLayout/NewDownload.html', encoding='utf-8') as f:
            fileContent = f.read()
        messageContent = fileContent.replace('[[LINK]]', str(url))
        message.attach(MIMEText(messageContent, 'html'))        
        message['Subject'] = "Neuer Download verfügbar"
        message['From'] = "Testzentrum Odenwaldkreis" + f' <{FROM_EMAIL}>'
        message['To'] = requester
        smtp = smtplib.SMTP(SMTP_SERVER,port=587)
        smtp.starttls()
        smtp.login(SMTP_USERNAME, SMTP_PASSWORD)
        logging.debug(
            "Sending Mail with following tupel: %s" % (message))
        smtp.send_message(message)
        logging.debug("Mail was send")
        smtp.quit()
        return True
    except Exception as err:
        logging.error("The following error occured in send mail download: %s" % (err))
        return False
