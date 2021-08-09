<?php
  class Connection{
    private $con_dat;
    private $con;
    private $sql;
    private $qry;
    private $tbl;
    private $dat=[];
    private $sym=["*", "\\", "\"", "'"];
    private $kw=["SELECT", "INSERT", "UPDATE", "DELETE", "TRUNCATE", "DROP"];

    public function __construct(
      string $db="",
      string $usr="root",
      string $pwd="",
      string $hst="localhost"
    ){ 
      return (
        $this->con = mysqli_connect(
          $this->con_dat["hst"]=$hst,
          $this->con_dat["usr"]=$usr,
          $this->con_dat["pwd"]=$pwd,
          $this->con_dat["db"]=$db
        )
      )?$this:false;
    }

    // -- Output
    // Data
    public function sim(){ // Simple
      try{
        $this->dat = mysqli_fetch_all($this->qry);
        return $this;
      }
      catch(Exception $e){
        return false;
      }
    }
    public function sim_unit(){ // Simple Single Row
      try{
        $this->dat = mysqli_fetch_row($this->qry);
        return $this;
      }
      catch(Exception $e){
        return false;
      }
    }
    public function asc(){ // Assoc
      try{
        $this->dat=[];
        while ($r=mysqli_fetch_assoc($this->qry))
        $this->dat[]=$r;
        return $this;
      }
      catch(Exception $e){
        return false;
      }
    }
    public function asc_unit(){ // Assoc SIngle Row
      try{
        $this->dat=mysqli_fetch_assoc($this->qry);
        return $this;
      }
      catch(Exception $e){
        return false;
      }
    }

    # Getter
    public function get(string $nm="dat"){
      switch($nm){
        case "tbl":
          return $this->tbl;
        case "qry":
          return $this->qry;
        case "id":
          return mysqli_insert_id($this->con);
        case "sql":
          return $this->sql;
        case "con":
          return $this->con;
        case "hst":
          return $this->con_dat["hst"];
        case "usr":
          return $this->con_dat["usr"];
        case "db":
          return $this->con_dat["db"];
        case "cnt":
          return mysqli_num_rows($this->qry);
        case "dat":
        default:
          return $this->dat;
      }
    }

    # filter var
    public function vld(string $var){
      # foreach ($kw as $k) if (strpos($var, $k)) return false; // Enable keyword filter by removing leading '#'
      // foreach ($var as $c){
      for ($i=0; $i<strlen($var);$i++){
        $c=$var[$i];
        if (in_array($c, $this->sym))
        return false;
      } return true;
    }
    # filter elements of array
    public function arr_vld(array $vars){
      foreach ($vars as $key=>$var){
        if(
          (is_array($var)? !$this->arr_vld($var): !$this->vld($var)) ||
          !$this->vld($key)
        ) return false;
      } return true;
    }
    # important builder method for converting array elements to be used in query.
    public function build(array $arr, int $mod=1){
      $out="";
      switch($mod){
        case 1:
          foreach($arr as $el)
          $out.="`$el`, ";
          $out=rtrim($out, ", ");
          break;
        case 2:
          foreach ($arr as $ky=>$el)
          $out.="`$ky`='$el' AND ";
          $out=rtrim($out, "AND ");
          break;
        case 3:
          foreach ($arr as $ky=>$el)
          if(is_array($el))
          foreach($el as $el_unit)
          $out.="`$ky`.`$el_unit`, ";
          else $out.="`$ky`.`$el`, ";
          $out=rtrim($out, ", ");
          break;
        case 4:
          foreach ($arr as $tbl=>$inarr){
            $tbls=array_keys($inarr);
            $flds=array_values($inarr);
            $out.=
              "INNER JOIN `$tbl` ON `".$tbls[0]."`.`".$flds[0]."`=".
              (
                is_int($tbls[1])?
                "'".$flds[1]."'":
                "`".$tbls[1]."`.`".$flds[1]."` "
              );
          }
          $out=rtrim($out);
          break;
        case 5:
          foreach ($arr as $inarr){
            $tbls=array_keys($inarr);
            $flds=array_values($inarr);
            $out.=
              "`".$tbls[0]."`.`".$flds[0]."`=".
              (
                is_int($tbls[1])?
                "'".$flds[1]."'":
                ("`".$tbls[1]."`.`".$flds[1]."`")
              )." AND ";
          }
          $out=rtrim($out, "AND ");
          break;
        case 6:
          foreach ($arr as $inarr){
            $subout="";
            foreach ($inarr as $vl){
              $subout.="'$vl', ";
            }
            $subout=rtrim($subout, ", ");
            $out.="($subout), ";
          }
          $out=rtrim($out, ", ");
          break;
        case 7:
          foreach ($arr as $ky=>$el)
          $out.="`$ky`='$el', ";
          $out=rtrim($out, ", ");
          break;
        default:
          return false;
      }
      return $out;
    }

    # run raw sql
    public function qry(string $sql){
      return (
        $this->qry=
        mysqli_query($this->con, $this->sql=$sql)
      )?$this:false;
    }

    # switch methods
    public function swdb(string $db){
      if($this->vld($db))
      return (
        $this->qry("USE ".$this->con_dat["db"]=$db)
      )?$this:false;
      return false;
    }
    public function swtbl(string $tbl){
      if(!$this->vld($tbl))
      return false;
      $this->tbl=$tbl;
      return true;
    }

    # create methods
    public function mkdb(string $db){
      if($this->vld($db))
      return (
        $this->qry("CREATE DATABASE ".$this->con_dat["db"]=$db)
      )?$this:false;
      return false;
    }
    public function insert(
      array $dat,
      string $tbl="",
      array $fld=[]
    ){
      // Table Name
      if($tbl==""){
        if(empty($this->tbl)) return false;
        $tbl=$this->tbl;
      } else $this->tbl=$tbl;
      $sql="";

      if(count($fld)==0) $fld="";
      else {
        if($this->arr_vld($fld)){
          if($fld=$this->build($fld)){
            $fld = "($fld)";
          }
          else return false;
        }
        else return false;
      }

      // Build Data
      if(count($dat)==0)
      return false;
      else {
        if($this->arr_vld($dat)){
          if(!($dat=$this->build($dat, 6)))
          return false;
        }
        else return false;
      }

      $sql="INSERT INTO `$tbl`$fld VALUES $dat";
      return $this->qry($sql);
    }

    # read methods
    public function select(
      array $dat=[],
      string $tbl="",
      array $cnd=[]
    ){
      // Table Name
      if($tbl==""){
        if(empty($this->tbl)) return false;
        $tbl=$this->tbl;
      } else $this->tbl=$tbl;
      $sql="";

      // Build Data
      if(count($dat)==0)
      $dat="*";
      else {
        if($this->arr_vld($dat)){
          if(!($dat=$this->build($dat)))
          return false;
        }
        else return false;
      }

      if(count($cnd)>0){
        // Build Condition
        if($this->arr_vld($cnd)){
          if(!($cnd=$this->build($cnd, 2)))
          return false;
        }
        else return false;
        $sql="SELECT $dat FROM `$tbl` WHERE $cnd";
      } else $sql="SELECT $dat FROM `$tbl`";

      return $this->qry($sql);
    }
    public function join_select(
      array $dat,
      array $jn,
      string $tbl="",
      array $cnd=[]
    ){
      // Table Name
      if($tbl==""){
        if(empty($this->tbl)) return false;
        $tbl=$this->tbl;
      } else $this->tbl=$tbl;
      $sql="";

      // Build Data
      if(count($dat)==0)
      $dat="*";
      else {
        if($this->arr_vld($dat)){
          if(!($dat=$this->build($dat, 3)))
          return false;
        }
        else return false;
      }

      // Build Join
      if(count($jn)==0) return false;
      if($this->arr_vld($jn)){
        if(!$jn=$this->build($jn, 4))
        return false;
      }
      else return false;

      if(count($cnd)>0){
        // Build Join Condition
        if($this->arr_vld($cnd)){
          if(!$cnd=$this->build($cnd, 5))
          return false;
        }
        else return false;
        $sql="SELECT $dat FROM `$tbl` $jn WHERE $cnd";
      } else $sql="SELECT $dat FROM `$tbl` $jn";

      return $this->qry($sql);
    }

    # update method
    public function update(
      array $dat,
      string $tbl="",
      array $cnd=[]
    ){
      // Table Name
      if($tbl==""){
        if(empty($this->tbl)) return false;
        $tbl=$this->tbl;
      } else $this->tbl=$tbl;
      $sql="";

      if(count($dat)==0) return false;
      if($this->arr_vld($dat)){
        if(!$dat=$this->build($dat, 7))
        return false;
      }
      else return false;

      if(count($cnd)>0){
        // Build Condition
        if($this->arr_vld($cnd)){
          if(!($cnd=$this->build($cnd, 2)))
          return false;
        }
        else return false;
        $sql="UPDATE `$tbl` SET $dat WHERE $cnd";
      } else $sql="UPDATE `$tbl` SET $dat";

      return $this->qry($sql);
    }

    # delete methods
    public function delete(array $cnd=[], string $tbl=""){
      // Table Name
      if($tbl==""){
        if(empty($this->tbl)) return false;
        $tbl=$this->tbl;
      } else $this->tbl=$tbl;
      $sql="";

      if(count($cnd)>0){
        // Build Condition
        if($this->arr_vld($cnd)){
          if(!($cnd=$this->build($cnd, 2)))
          return false;
        }
        else return false;
        $sql="DELETE FROM `$tbl` WHERE $cnd";
      } else $sql="DELETE FROM `$tbl`";
      return $this->qry($sql);
    }
    public function truncate(string $tbl=""){
      // Table Name
      if($tbl==""){
        if(empty($this->tbl)) return false;
        $tbl=$this->tbl;
      } else $this->tbl=$tbl;

      $sql="TRUNCATE `$tbl`";
      return $this->qry($sql);
    }
    public function drop(string $tbl=""){
      // Table Name
      if($tbl==""){
        if(empty($this->tbl)) return false;
        $tbl=$this->tbl;
      } else $this->tbl=$tbl;

      $sql="DROP TABLE `$tbl`";
      return $this->qry($sql);
    }
    public function dropdb(string $db=""){
      // Table Name
      if($db==""){
        if(empty($this->con_dat["db"])) return false;
        $db=$this->con_dat["db"];
      }

      $sql="DROP DATABASE `$tbl`";
      return $this->qry($sql);
    }
  }

  /*
    - Abdullah
    - Apr 12, 2020
  */
?>
