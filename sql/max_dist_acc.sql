CREATE OR REPLACE FUNCTION max_dist_acc(p_child_id IN NUMBER) 
RETURN VARCHAR2
IS
    v_child_lat      DATA.latitude%TYPE;
    v_child_lon      DATA.longitude%TYPE;
    v_max_dist       NUMBER := 0;
    v_acc_lat        ACCIDENTS.latitude%TYPE;
    v_acc_lon        ACCIDENTS.longitude%TYPE;
    v_current_dist   NUMBER;
    v_child_count    number;
    v_acc_count      number;
    v_max_desc       ACCIDENTS.description%TYPE := NULL;
    e_no_data        EXCEPTION;
    e_no_accidents   EXCEPTION;
    
BEGIN

    SELECT COUNT(*) INTO v_child_count
    FROM data
    WHERE child_id = p_child_id;
  
    IF v_child_count = 0 THEN
        raise e_no_data;
    END IF;
    
    SELECT latitude, longitude INTO v_child_lat, v_child_lon
    FROM data
    WHERE child_id = p_child_id;
    
    SELECT COUNT(*) 
    INTO v_acc_count
    FROM accidents;
  
    IF v_acc_count = 0 THEN
        raise e_no_accidents;
    END IF;

    FOR i IN (SELECT latitude AS acc_lat, longitude AS acc_lon, description FROM accidents) LOOP
        v_acc_lat := i.acc_lat;
        v_acc_lon := i.acc_lon;

        v_current_dist := sqrt( power(v_child_lat - v_acc_lat, 2 ) + power(v_child_lon - v_acc_lon, 2 ));

        IF v_current_dist > v_max_dist THEN
            v_max_dist := v_current_dist;
            v_max_desc := i.description;
        END IF;
    END LOOP;

    RETURN TO_CHAR(ROUND(v_max_dist,2), 'FM999990.00') || ' - ' || v_max_desc;

EXCEPTION
  WHEN e_no_data THEN
    DBMS_OUTPUT.PUT_LINE('Nu exista nicio inregistrare in DATA pentru copilul cu id = '|| p_child_id);
    RETURN NULL;
    
  WHEN e_no_accidents THEN
    DBMS_OUTPUT.PUT_LINE('Nu exista niciun rand în tabelul ACCIDENTS.');
    RETURN NULL;
END max_dist_acc;
/