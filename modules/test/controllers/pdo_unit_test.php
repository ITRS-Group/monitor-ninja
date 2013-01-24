<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Unit_Test controller.
 */
class pdo_unit_test_Controller extends Controller {

    private $db = NULL;
    const ALLOW_PRODUCTION = FALSE;

    public function index($user=false)
    {
        $authentic = new Auth;
        Auth::instance()->force_login($user);

        // Run tests and show results!
        echo new Ninja_Unit_Test;
    }


    private function connect()
    {
        $this->db = Database::instance();
    }
    private function try_list_tables($require=true,$verbose=false)
    {
        $tlist = $this->db->list_tables();
        $n = count($tlist);
        if( !$n ) {
            echo "No tables!\n";
            if($require)
            {
                throw new Kohana_Exception('unit_test.failed',
                                           "Table count is 0.");
            }
        }
        else
        {
            echo $n." table(s)\n";
            if( $verbose ) {
                foreach( $tlist as $t ) {
                    echo "\t".$t."\n";
                }
            }
        }
    }

    private $myTables = array(
        't1' => array(
             'create' => "create table t1(i int NOT NULL, s varchar(32), PRIMARY KEY(i))",
             'fieldCount' => 2
             ),
        't2' => array(
                      'create' => "create table t2(s varchar(32),f float(6,2) DEFAULT 42.24)",
                      'fieldCount' => 2
                      )
        );

    private function cleanup_db($requireExists)
    {
        foreach($this->myTables as $t => $meta) {
            $sql = "DROP TABLE $t";
            try {
                $this->db->query($sql);
            }
            catch(Exception $e)
            {
                if( $requireExists ) throw $e;
            }
        }
        if(0)
        {
            /* DAMMIT:

            The built-in MySQL driver for Kahona caches the table
            list, so this MIGHT fail when using that back-end. In my
            tests it fails every 2nd run, but why only every 2nd,
            i'm not sure.
            */
            $tlist = $this->db->list_tables();
            $n = count($tlist);
            if( 0 != $n ) {
                echo "Still have $n table(s) after cleaning up!\n";
                throw new Kohana_Exception('unit_test.failed',
                                           "Could not remove all tables!");
            }
        }
    }

    private function create_tables()
    {
        echo "Creating some tables...\n";
        $oldCount = count($this->db->list_tables());
        $expectCount = $oldCount;
        foreach( $this->myTables as $t => $meta )
        {
            $sql = $meta['create'];
            echo "\t$sql\n";
            $this->db->query($sql);
            ++$expectCount;
        }
        $conn = Kohana::config('database.default.connection');
        if( 'pdogeneric' == $conn['type'] )
        {
            /*
             Some of the drivers cache the table list, which
             is bad for this test. So we only do this test for
             the pdogeneric driver.
            */
            $newCount = count($this->db->list_tables());
            if( $expectCount != $newCount )
            {
                $msg = "ERROR: Expecting $expectCount tables but i see $newCount!";
                echo $msg."\n";
                throw new Kohana_Exception('unit_test.failed',
                                           $msg);
            }
        }
        else
        {
            echo "WARNING: Skipping table-count test b/c of suspected cached table list.\n";
        }
        echo "Tables created.\n";

    }

    private function get_fields($table,$requireCount)
    {
        $ar = $this->db->list_fields($table);
        $n = count($ar);
        if( $requireCount && ($requireCount != $n ))
        {
            $msg = "WRONG FIELD COUNT: expecting $requireCount but got $n!";
            echo "$msg\n";
            throw new Kohana_Exception('unit_test.failed', $msg );
        }
        return $ar;
    }

    private function try_list_fields()
    {
        foreach( $this->myTables as $t => $meta )
        {
            $n = $meta['fieldCount'];
            $f = $this->get_fields($t,$n);
            echo "Field list for table [$t]:\n";
            print_r($f);
        }
    }

    private function try_field_data()
    {
        foreach( array('t1'=>2,
                       't2'=>2)
                 as $t => $n ) {
            $f = $this->db->field_data($t);
            echo "Field data for table [$t]:\n";
            print_r($f);
        }
    }

    private function try_query()
    {
        $q = $this->db->query('SELECT COUNT(*) from t1');
        $q->result(TRUE);
        $row = $q->current();
        print_r($row);
    }

    /**
     *
     *
     */
    public function allTests()
    {
        /* i'd like to print out "you're using driver so-and-so", but
         i can't find a way to get that info from Kohana, and $config
         is apparently emptied (as a security measure?) before this is
         called.
        */
        try
        {
            $this->connect();
            echo "Connection parameters:\n";
            print_r(Kohana::config('database.default.connection'));
            $this->cleanup_db(false);
            $this->create_tables();
            $this->try_list_tables(true,true);
            $this->try_list_fields();
            $this->try_field_data();
            $this->try_query();
            $this->cleanup_db(true);
            $this->try_list_tables(false,false);
        }
        catch(Exception $e)
        {
            echo "EXCEPTION: ".$e->getMessage()."\n";
            throw $e;
        }
        echo "All tests passed. Hooray!\n";
    }

    public function reports_test_crash($msg)
    {
        echo __FILE__.": $msg\n";
        exit(1);
    }

}
