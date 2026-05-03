<?php
class TownsIndex
{
    const PLY_MAX = 1000;

    private function townsSelect($a)
    {
        $b = first(
            'SELECT COUNT(*) as count
            FROM users u
            INNER JOIN build b
                on b.id = u.buildmy
            WHERE b.town=' . $a . '
            AND u.online=1');
        return $b['count'];
    }

    public function town($id)
    {
        $a = $this->townsSelect($id);
        $res = $a > 0?(ceil(100 / self::PLY_MAX * $a) > 100)? 100: ceil(100 / self::PLY_MAX * $a):0;
        return array(
            1=>$res,
            2=>$a
        );
    }
    public function updateOffline()
    {
        $off = time() - 5*60;
        update('users',array('online'=>'0'),'online=1 AND onlinetime<='.(int)$off);
    }
    public function updateOnline()
    {
        $on = time() + 10;
        update('users',array('online'=>'1', 'onlinetime'=>$on),'id='.(int)$_SESSION['id']);
        delete('sparka','time<='.(int)time());
    }
    public function deletFunct()
    {
        $timer = time();
        delete('pvp_zayv','time<='.(int)$timer);
        //delete('status_user','time_status<='.(int)$dop_pr_time.' AND id_user='.(int)$_SESSION['id']);
        delete('items_users','dattimer<='.(int)$timer.' AND dattimer != "not" AND user_id='.(int)$_SESSION['id']);
        delete('items_poke','datetime<='.(int)$timer.' AND datetime != "not"');
        delete('baf_pokes','times<='.(int)$timer);
    }
    public function eggVilup()
    {
        $timer = time();
        $eggIsset = select('SELECT dtime,id_egg,users_egg FROM eggs WHERE dtime<=%d AND users_egg <> 3 AND users_egg=%d ORDER BY id_egg ASC LIMIT 0, 10',$timer,$_SESSION['id']);
        if(!empty($eggIsset)){
           foreach($eggIsset as $eggIssetRow){
              if($eggIssetRow['dtime'] <= $timer){
                if(pokemonEggPluse($eggIssetRow['users_egg'],$eggIssetRow['id_egg'])){
                  delete('eggs','users_egg='.(int)$eggIssetRow['users_egg'].' AND  id_egg='.(int)$eggIssetRow['id_egg']);
                }else{
                  // not...
                }
              }
           }
        }
    }
}



class OnlineIndex
{
    public function onlineUsers()
    {
        $a = first('
        SELECT COUNT(*) as count
        FROM users
        WHERE online=1');
        return $a['count'];
    }
    public function registrUsers()
    {
        $a = first('
        SELECT COUNT(*) as count
        FROM users');
        return $a['count'];
    }
    public function inputUsers()
    {
        $dat = date('Y-m-d');
        //insert('inputusers',array('userid'=>1,'data'=>$dat));
        $a = first('
        SELECT COUNT(*) as count
        FROM inputusers 
        WHERE data="%s"',$dat);
        return $a['count'];
    }
}

class RankingsIndex
{
   public function getTopRang(){
    return select('SELECT id, rang_b FROM users WHERE rang_b > 0 AND activation=1 AND groups NOT IN (1,7) ORDER BY rang_b DESC LIMIT 10');
}



    public function getTopMoney()
    {
        return select('SELECT iu.count, u.id 
                       FROM items_users iu 
                       INNER JOIN users u ON u.id = iu.user_id 
                       WHERE iu.item_id = 1 AND iu.count > 1000000 
                       AND u.groups != 1 AND u.groups != 7 
                       ORDER BY iu.count DESC LIMIT 10');
    }

    public function getTopDex()
    {
        return select('SELECT count_poke, id 
                       FROM users 
                       WHERE activation = 1 AND id != 3 AND groups != 1 AND groups != 7 
                       AND count_poke > 1 
                       ORDER BY count_poke DESC LIMIT 10');
    }

    public function getTopShinyDex()
    {
        return select('SELECT count_poke_s, id 
                       FROM users 
                       WHERE activation = 1 AND id != 3 AND groups != 1 AND groups != 7 
                       AND count_poke_s > 0 
                       ORDER BY count_poke_s DESC LIMIT 10');
    }
}


?>