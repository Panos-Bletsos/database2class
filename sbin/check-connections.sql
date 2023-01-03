-- Use this query to check how many connections the app has opened at the DB.
SELECT * FROM `mysql.general_log` where user_host like "%db2php%" and command_type="Connect";
