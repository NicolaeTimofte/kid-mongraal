CREATE TABLE users (
    id NUMBER PRIMARY KEY,
    username VARCHAR2(50) UNIQUE NOT NULL,
    email VARCHAR2(100) UNIQUE NOT NULL,
    password VARCHAR2(255) NOT NULL,
    address VARCHAR2(255),
    latitude NUMBER,
    longitude NUMBER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE SEQUENCE users_seq START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

CREATE OR REPLACE TRIGGER trg_users_id
BEFORE INSERT ON users
FOR EACH ROW
WHEN (NEW.id IS NULL)
BEGIN
    SELECT users_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/

CREATE TABLE children (
    id NUMBER PRIMARY KEY,
    user_id NUMBER NOT NULL,
    birthdate DATE,
    first_name VARCHAR2(50),
    last_name VARCHAR2(50),
    gender VARCHAR2(10),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE SEQUENCE children_seq START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

CREATE OR REPLACE TRIGGER trg_children_id
BEFORE INSERT ON children
FOR EACH ROW
WHEN (NEW.id IS NULL)
BEGIN
    SELECT children_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/

CREATE TABLE data (
    id NUMBER PRIMARY KEY,
    child_id NUMBER NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    location VARCHAR2(100),
    latitude NUMBER,
    longitude NUMBER,
    FOREIGN KEY (child_id) REFERENCES children(id)
);

CREATE SEQUENCE data_seq START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

CREATE OR REPLACE TRIGGER trg_data_id
BEFORE INSERT ON data
FOR EACH ROW
WHEN (NEW.id IS NULL)
BEGIN
    SELECT data_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/

CREATE TABLE accidents (
    id NUMBER PRIMARY KEY,
    location VARCHAR2(100),
    latitude NUMBER,
    longitude NUMBER,
    type VARCHAR2(100),
    description VARCHAR2(255)
);

CREATE SEQUENCE accidents_seq START WITH 1 INCREMENT BY 1 NOCACHE NOCYCLE;

CREATE OR REPLACE TRIGGER trg_accidents_id
BEFORE INSERT ON accidents
FOR EACH ROW
WHEN (NEW.id IS NULL)
BEGIN
    SELECT accidents_seq.NEXTVAL INTO :NEW.id FROM dual;
END;
/

CREATE TABLE accident_daily_count (
  accident_date DATE PRIMARY KEY,
  total_accidents NUMBER
);

CREATE OR REPLACE TRIGGER trg_accident_daily_count
FOR INSERT ON accidents
COMPOUND TRIGGER

  v_insert_count  number := 0;

  BEFORE EACH ROW IS
  BEGIN
    v_insert_count := v_insert_count + 1;
  END BEFORE EACH ROW;

  AFTER STATEMENT IS
  BEGIN
    DECLARE
      v_today DATE := TRUNC(SYSDATE); 
      v_rows  INTEGER;
    BEGIN
      UPDATE accident_daily_count
         SET total_accidents = total_accidents + v_insert_count
       WHERE accident_date = v_today;

      v_rows := SQL%ROWCOUNT;
      IF v_rows = 0 THEN
        INSERT INTO accident_daily_count (accident_date, total_accidents)
        VALUES (v_today, v_insert_count);
      END IF;
    END;
  END AFTER STATEMENT;

END trg_accident_daily_count;
/