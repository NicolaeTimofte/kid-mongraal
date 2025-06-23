CREATE OR REPLACE FUNCTION dist_parinte(p_child_id IN NUMBER) 
RETURN VARCHAR2
IS
    v_child_lat      DATA.latitude%TYPE;
    v_child_lon      DATA.longitude%TYPE;
    v_dist       NUMBER := NULL;
    v_parinte_lat        USERS.latitude%TYPE;
    v_parinte_lon        USERS.longitude%TYPE;
    v_child_count    number;
    v_acc_count      number;
    e_no_data        EXCEPTION;
    e_no_accidents   EXCEPTION;
    
BEGIN

    SELECT COUNT(*) INTO v_child_count
    FROM data
    WHERE child_id = p_child_id;
  
    IF v_child_count = 0 THEN
        raise e_no_data;
    END IF;
    
    SELECT latitude, longitude
    INTO v_child_lat, v_child_lon
    FROM (
        SELECT latitude, longitude
        FROM data
        WHERE child_id = p_child_id
        ORDER BY timestamp DESC
    )
    WHERE ROWNUM = 1;

    SELECT latitude, longitude 
    INTO v_parinte_lat, v_parinte_lon
    FROM users JOIN children 
    ON users.id=children.user_id 
    WHERE children.id=p_child_id;

    v_dist := sqrt( power(v_child_lat - v_parinte_lat, 2 ) + power(v_child_lon - v_parinte_lon, 2 ));

    RETURN TO_CHAR(ROUND(v_dist,2), 'FM999990.00');

EXCEPTION
  WHEN e_no_data THEN
    DBMS_OUTPUT.PUT_LINE('Nu exista nicio inregistrare in DATA pentru copilul cu id = '|| p_child_id);
    RETURN NULL;
END dist_parinte;
/