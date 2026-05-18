<?php
    try {
        $username="if0_41822736";
        $password="Joerenrey21";
        $charset = 'utf8mb4';
        $db="mysql:host=sql302.infinityfree.com;dbname=if0_41822736_inlandflow;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $conn = new PDO($db, $username, $password, $options);
    } catch (PDOException $error) {
        echo $error->getMessage();
        die();
    }

    // For Map
    function get_db(){
        try {
            $username="if0_41822736";
            $password="Joerenrey21";
            $charset = 'utf8mb4';
            $db="mysql:host=sql302.infinityfree.com;dbname=if0_41822736_inlandflow;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $conn = new PDO($db, $username, $password, $options);
            return $conn;
        } catch(PDOException $e){
            echo 'Connection Failed: ' . $e->getMessage();
        }
    }

    function get_map_data(){
        try {
            $db = get_db();
            $stmt = $db->query("SELECT name, lat, lon, address FROM tb_location");
            $data = $stmt->fetchAll();
            return json_encode($data);
        } catch(PDOException $e){
            return 'Error: ' . $e->getMessage();
        }
    }
    
    // Helper functions for analytics
    function getBookingAnalytics($resortid = null, $period = 'month') {
        global $conn;
        $sql = "SELECT DATE(checkindate) as date, COUNT(*) as bookings, SUM(num_adults + num_kids) as guests 
                FROM tb_cart WHERE cart_status = 'Place Order'";
        if($resortid) {
            $sql .= " AND resortid = :resortid";
        }
        if($period == 'month') {
            $sql .= " AND MONTH(checkindate) = MONTH(CURRENT_DATE())";
        } elseif($period == 'year') {
            $sql .= " AND YEAR(checkindate) = YEAR(CURRENT_DATE())";
        }
        $sql .= " GROUP BY DATE(checkindate)";
        $stmt = $conn->prepare($sql);
        if($resortid) $stmt->bindParam(':resortid', $resortid);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    function getRevenueAnalytics($resortid = null, $period = 'month') {
        global $conn;
        $sql = "SELECT DATE(c.checkindate) as date, SUM(po.total_fee) as revenue 
                FROM tb_placed_order po 
                JOIN tb_cart c ON po.cart_id = c.cart_id 
                WHERE po.reservation_status = 'Completed'";
        if($resortid) {
            $sql .= " AND c.resortid = :resortid";
        }
        if($period == 'month') {
            $sql .= " AND MONTH(c.checkindate) = MONTH(CURRENT_DATE())";
        } elseif($period == 'year') {
            $sql .= " AND YEAR(c.checkindate) = YEAR(CURRENT_DATE())";
        }
        $sql .= " GROUP BY DATE(c.checkindate)";
        $stmt = $conn->prepare($sql);
        if($resortid) $stmt->bindParam(':resortid', $resortid);
        $stmt->execute();
        return $stmt->fetchAll();
    }
?>