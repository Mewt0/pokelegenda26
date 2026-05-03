<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Список квестов</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8B2323;
            --secondary: #FFD700;
            --accent: #A020F0;
            --success: #00FF00;
            --info: #00C5CD;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --gray: #6c757d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #2c3e50, #4a6491);
            color: var(--light);
            min-height: 100vh;
            padding: 20px;
        }
        
        .quest-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .quest-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            background: rgba(26, 14, 14, 0.85);
            border-radius: 15px;
            border: 2px solid var(--secondary);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }
        
        .quest-header h1 {
            font-size: 2.5rem;
            color: var(--secondary);
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }
        
        .no-quests {
            text-align: center;
            background: rgba(26, 14, 14, 0.7);
            padding: 30px;
            border-radius: 15px;
            border: 2px solid var(--primary);
            font-size: 1.5rem;
            margin-bottom: 30px;
        }
        
        .quest-item {
            background: rgba(26, 14, 14, 0.7);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 2px solid var(--accent);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }
        
        .quest-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(160, 32, 240, 0.4);
        }
        
        .quest-title {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 10px;
            border-radius: 10px;
            background: rgba(139, 35, 35, 0.3);
            transition: background 0.3s ease;
        }
        
        .quest-title:hover {
            background: rgba(139, 35, 35, 0.5);
        }
        
        .quest-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--secondary);
            color: var(--dark);
            border-radius: 50%;
            font-weight: bold;
            font-size: 1.5rem;
            margin-right: 15px;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }
        
        .quest-name {
            font-size: 1.8rem;
            color: var(--secondary);
            text-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
            flex-grow: 1;
        }
        
        .quest-icon {
            font-size: 1.5rem;
            color: var(--secondary);
            transition: transform 0.3s ease;
        }
        
        .quest-content {
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 215, 0, 0.3);
        }
        
        .quest-description {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(30, 30, 60, 0.4);
            border-radius: 10px;
            border-left: 4px solid var(--info);
        }
        
        .quest-task-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1.4rem;
            color: var(--secondary);
        }
        
        .quest-task-icon {
            margin-right: 10px;
            color: var(--success);
        }
        
        .quest-task {
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(30, 30, 60, 0.4);
            border-radius: 10px;
            border-left: 4px solid var(--success);
        }
        
        .pokemon-task {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(40, 40, 70, 0.5);
            border-radius: 8px;
        }
        
        .pokemon-info {
            flex-grow: 1;
            font-size: 1.1rem;
        }
        
        .pokemon-image {
            width: 80px;
            height: 80px;
            margin-right: 15px;
            border-radius: 8px;
            border: 2px solid var(--secondary);
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .pokemon-image img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .progress-toggle {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            background: rgba(52, 152, 219, 0.3);
            margin-top: 15px;
            transition: background 0.3s ease;
        }
        
        .progress-toggle:hover {
            background: rgba(52, 152, 219, 0.5);
        }
        
        .progress-toggle-text {
            font-size: 1.3rem;
            color: var(--info);
            margin-left: 10px;
        }
        
        .progress-container {
            padding: 20px 0;
        }
        
        .progress-bar {
            height: 35px;
            background: rgba(30, 30, 60, 0.8);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.5);
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            color: white;
            text-shadow: 0 0 3px rgba(0, 0, 0, 0.8);
            transition: width 1s ease-in-out;
        }
        
        .progress-text {
            position: absolute;
            width: 100%;
            text-align: center;
            font-weight: bold;
            font-size: 1.1rem;
            color: white;
            text-shadow: 0 0 3px rgba(0, 0, 0, 0.8);
        }
        
        @media (max-width: 768px) {
            .quest-container {
                padding: 10px;
            }
            
            .quest-name {
                font-size: 1.5rem;
            }
            
            .pokemon-task {
                flex-direction: column;
                text-align: center;
            }
            
            .pokemon-image {
                margin-right: 0;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="quest-container">
        <div class="quest-header">
            <h1><i class="fas fa-tasks"></i> Ваши квесты</h1>
        </div>
        
        <?php
        function quest_text_proc($pr,$id){
            $p = first('SELECT text FROM quest_process WHERE process=%d AND q_id=%d',$pr,$id);
            $resultstiks = ($p?$p['text']:false);
            return $resultstiks;
        }
        
        echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
        $q_my = select('SELECT q.process, q.gotov, q.quest_id, ql.dop, ql.name_q, ql.text FROM quest q INNER JOIN quest_list ql on q.quest_id=ql.id_q WHERE q.user_id=%d ORDER BY q.quest_id ASC',$_SESSION['id']);
        
        if(empty($q_my)): ?>
            <div class="no-quests">
                <i class="fas fa-inbox fa-3x" style="color: #FFD700; margin-bottom: 20px;"></i>
                <p>В данный момент у вас нет активных квестов.</p>
                <p>Посетите доску заданий, чтобы получить новый квест!</p>
            </div>
        <?php endif; 
        
        $p = 1;
        if(!empty($q_my)):
            foreach($q_my as $q_my_f):
                $procent = ($q_my_f['process']/2) * 10;
                if($procent > 100) $procent = 100;
                if($q_my_f['gotov'] == 1) $procent = 100;
                
                // Определение цвета прогресс-бара
                if($procent > 99) $colour = "#FFD700";
                elseif($procent > 80) $colour = "#A020F0";
                elseif($procent > 50) $colour = "#00FF00";
                elseif($procent > 25) $colour = "#00C5CD";
                else $colour = "#8B2323";
                
                $ps = $p++;
                $p_txt = quest_text_proc($q_my_f['process'],$q_my_f['quest_id']);
        ?>
                <div class="quest-item">
                    <div class="quest-title" id="quest_<?php echo $ps; ?>">
                        <div class="quest-number"><?php echo $ps; ?></div>
                        <div class="quest-name"><?php echo $q_my_f['name_q']; ?></div>
                        <i class="fas fa-chevron-down quest-icon"></i>
                    </div>
                    
                    <div id="quest_hide<?php echo $ps; ?>" class="quest-content">
                        <div class="quest-description">
                            <?php echo $q_my_f['text']; ?>
                        </div>
                        
                        <div class="quest-task-header">
                            <i class="fas fa-bullseye quest-task-icon"></i>
                            <span><?=($q_my_f['gotov']>0?'Награда':'Задача');?></span>
                        </div>
                        
                        <div class="quest-task">
                            <?php 
                            if($q_my_f['dop'] == 1):
                                $pqp = select('SELECT * FROM quest_poke WHERE questid=%d AND userid=%d',$q_my_f['quest_id'],$_SESSION['id']);
                                if(!empty($pqp)):
                                    foreach($pqp as $pqprow):
                                        $bid = $pqprow['pokenum'];
                                        $format = ($bid>493?'png':'gif');
                            ?>
                                        <div class="pokemon-task">
                                            <div class="pokemon-image">
                                                <img src="pok/anim/<?php echo $bid.'.'.$format; ?>" alt="Покемон #<?php echo $bid; ?>">
                                            </div>
                                            <div class="pokemon-info">
                                                <b>Побеждено покемонов #<?php echo $bid; ?>: 
                                                <span style="color:#FF6347;"><?php echo $pqprow['coolpokemin']; ?></span> 
                                                из <span style="color:#7CFC00;"><?php echo $pqprow['coolpokemax']; ?></span></b>
                                            </div>
                                        </div>
                            <?php 
                                    endforeach;
                                endif;
                            else:
                            ?>
                                <b><font color="#A52A2A"><?php echo $p_txt; ?></font></b>
                            <?php endif; ?>
                        </div>
                        
                        <div class="progress-toggle" id="quest_clicker_menu<?php echo $ps; ?>">
                            <i class="fas fa-chart-line"></i>
                            <span class="progress-toggle-text">Показать прогресс</span>
                        </div>
                        
                        <div id="quest_hide_menu<?php echo $ps; ?>" class="progress-container">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $procent; ?>%; background: <?php echo $colour; ?>;">
                                    <?php echo $procent; ?>%
                                </div>
                                <div class="progress-text"><?php echo $procent; ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>
        <?php 
            endforeach;
        endif; 
        ?>
    </div>
    
    <script>
        <?php if(!empty($q_my)): ?>
            $(document).ready(function() {
                <?php for($i = 1; $i <= $ps; $i++): ?>
                    $("#quest_hide<?php echo $i; ?>").hide();
                    $("#quest_hide_menu<?php echo $i; ?>").hide();
                    
                    $("#quest_<?php echo $i; ?>").click(function() {
                        $("#quest_hide<?php echo $i; ?>").slideToggle("slow", function() {
                            $("#quest_<?php echo $i; ?> .quest-icon").toggleClass("fa-chevron-down fa-chevron-up");
                        });
                    });
                    
                    $("#quest_clicker_menu<?php echo $i; ?>").click(function() {
                        $("#quest_hide_menu<?php echo $i; ?>").slideToggle("slow");
                    });
                <?php endfor; ?>
            });
        <?php endif; ?>
    </script>
</body>
</html>