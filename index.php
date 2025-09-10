<?php
require_once("defines.php");
require_once("pomm_conf.php");
require_once("func.php");
require_once("map_english.php");
?>
<style type="text/css">
    @font-face {
        font-family: 'K_Quest';
        src: url('../template/battleforazeroth/fonts/K_Quest.TTF') format('truetype');
        font-weight: normal;
        font-style: normal;
    }
    body { margin: 0; padding: 0; overflow: hidden; background-color: #000; }
            .zoom-container {
                width: 100%;
                height: 100%;
            }
            .map-container { 
                position: relative; 
                width: 100%; 
                height: 100%;
                background-size: contain;
                background-repeat: no-repeat;
                background-position: center;
            }
            #world, #outland, #northrend {
                position: absolute;
                width: 100%;
                height: 100%;
                background-size: contain;
                background-repeat: no-repeat;
                background-position: center;
            }    #world { background-image: url(img/map/azeroth.jpg); z-index: 10; }
    #outland { background-image: url(img/map/outland.jpg); z-index: 9; visibility: hidden; }
    #northrend { background-image: url(img/map/northrend.jpg); z-index: 8; visibility: hidden; }
    #pointsOldworld, #pointsOutland, #pointsNorthrend {
        position: absolute;
        width: 100%;
        height: 100%;
        z-index: 100;
    }
    #info_bottom { position: absolute; top: 20px; width: 100%; text-align: center; z-index: 101; }
    #server_info {
        font-family: 'K_Quest', sans-serif;
        font-size: 22px;
        text-shadow: 1px 1px 2px #000;
        display: inline-flex;
        gap: 15px; /* Add space between the boxes */
    }
    #server_info span {
        cursor: pointer;
        transition: color 0.3s, background-color 0.3s, box-shadow 0.3s;
        padding: 8px 20px;
        border-radius: 5px;
        background-color: rgba(0, 0, 0, 0.6);
        border: 1px solid;
        box-shadow: 0 0 8px rgba(255, 255, 255, 0.2);
    }
    #server_info span:hover {
        color: #FFF;
        box-shadow: 0 0 12px rgba(255, 255, 255, 0.5);
    }

    /* --- Map Specific Colors --- */
    /* Azeroth (Yellow) */
    #server_info span:nth-child(1) {
        color: #FFFF99;
        border-color: #EABA28;
    }
    #server_info span:nth-child(1).active {
        color: #FFF;
        background-color: rgba(234, 186, 40, 0.4);
        box-shadow: 0 0 15px rgba(234, 186, 40, 0.7);
    }

    /* Outland (Green) */
    #server_info span:nth-child(2) {
        color: #AAD372;
        border-color: #2E8B57;
    }
    #server_info span:nth-child(2).active {
        color: #FFF;
        background-color: rgba(46, 139, 87, 0.4);
        box-shadow: 0 0 15px rgba(46, 139, 87, 0.7);
    }

    /* Northrend (Blue) */
    #server_info span:nth-child(3) {
        color: #3FC7EB;
        border-color: #0070DD;
    }
    #server_info span:nth-child(3).active {
        color: #FFF;
        background-color: rgba(0, 112, 221, 0.4);
        box-shadow: 0 0 15px rgba(0, 112, 221, 0.7);
    }
    #tip { position: absolute; border: 1px solid #EABA28; background: rgba(0,0,0,0.8); color: #FFF; padding: 5px 10px; border-radius: 3px; display: none; z-index: 200; }
    .player-dot {
        position: absolute;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        border: 1px solid #000;
        box-shadow: 0 0 5px #FFF, 0 0 10px #FFF;
    }
    .class-1 { background-color: #C69B6D; } /* Warrior */
    .class-2 { background-color: #F48CBA; } /* Paladin */
    .class-3 { background-color: #AAD372; } /* Hunter */
    .class-4 { background-color: #FFF468; } /* Rogue */
    .class-5 { background-color: #FFFFFF; } /* Priest */
    .class-6 { background-color: #C41E3A; } /* Death Knight */
    .class-7 { background-color: #0070DD; } /* Shaman */
    .class-8 { background-color: #3FC7EB; } /* Mage */
    .class-9 { background-color: #8788EE; } /* Warlock */
    .class-11 { background-color: #FF7C0A; } /* Druid */
</style>

<div class="zoom-container">
    <div class="map-container">
        <div id="world">
            <div id="pointsOldworld"></div>
        </div>
        <div id="outland">
            <div id="pointsOutland"></div>
        </div>
        <div id="northrend">
            <div id="pointsNorthrend"></div>
        </div>
    </div>
</div>

<div id="tip"></div>

<div id="info_bottom">
    <div id="server_info"></div>
</div>

<script type="text/javascript">
    var maps_name_array = <?php echo json_encode(array_values($lang_defs['maps_names'])); ?>;
    var maps_for_points = [0, 1, 530, 571, 609];
    var outland_inst = [540,542,543,544,545,546,547,548,550,552,553,554,555,556,557,558,559,562,564,565];
    var northrend_inst = [533,574,575,576,578,599,600,601,602,603,604,608,615,616,617,619,624,631,632,649,650,658,668,724];

    function get_player_position(x, y, m, mapWidth, mapHeight) {
        var pos = { x: 0, y: 0 };
        var where_530 = 0;
        x = Math.round(x);
        y = Math.round(y);

        // Original map dimensions
        const originalMapWidth = 966;
        const originalMapHeight = 732;

        // Calculate scaling factors
        const scaleX = mapWidth / originalMapWidth;
        const scaleY = mapHeight / originalMapHeight;

        if (m == 530) {
            if (y < -1000 && y > -10000 && x > 5000) { x -= 10349; y += 6357; where_530 = 1; }
            else if (y < -7000 && x < 0) { x += 3961; y += 13931; where_530 = 2; }
            else { x -= 3070; y -= 1265; where_530 = 3; }
        } else if (m == 609) { x -= 2355; y += 5662; }

        var xpos = (where_530 == 3 || m == 571) ? Math.round(x * (m == 571 ? 0.050085 : 0.051446)) : Math.round(x * 0.025140);
        var ypos = (where_530 == 3 || m == 571) ? Math.round(y * (m == 571 ? 0.050085 : 0.051446)) : Math.round(y * 0.025140);

        switch (String(m)) {
            case '530':
                if (where_530 == 1) { pos.x = 858 - ypos; pos.y = 84 - xpos; }
                else if (where_530 == 2) { pos.x = 103 - ypos; pos.y = 261 - xpos; }
                else { pos.x = 684 - ypos; pos.y = 229 - xpos; }
                break;
            case '571': pos.x = 505 - ypos; pos.y = 642 - xpos; break;
            case '609': pos.x = 896 - ypos; pos.y = 232 - xpos; break;
            case '1': pos.x = 194 - ypos; pos.y = 398 - xpos; break;
            case '0': pos.x = 752 - ypos; pos.y = 291 - xpos; break;
            default: pos.x = 194 - ypos; pos.y = 398 - xpos;
        }

        // Scale the final position
        pos.x *= scaleX;
        pos.y *= scaleY;

        return pos;
    }

    // Global variables to store map dimensions
    let currentMapWidth = 0;
    let currentMapHeight = 0;

    function resizeMap() {
        const mapContainer = document.querySelector('.map-container');
        if (mapContainer) {
            currentMapWidth = mapContainer.offsetWidth;
            currentMapHeight = mapContainer.offsetHeight;
            console.log('Map resized. Dimensions:', currentMapWidth, 'x', currentMapHeight);
            // Re-render players if they are already loaded
            if (window.playerData) { // Assuming playerData is stored globally or passed
                show(window.playerData);
            }
        }
    }

    function show(players) {
        console.log('show() called with:', players);
        if (!players || !Array.isArray(players)) {
            return;
        }
        window.playerData = players; // Store player data globally for resize

        var pointsHTML = { 0: '', 1: '', 2: '' };
        
        players.forEach(player => {
            var mapId = parseInt(player.map);
            var extention = 0;
            if ((mapId == 530 && player.position_y > -1000) || outland_inst.indexOf(mapId) > -1) {
                extention = 1;
            } else if (mapId == 571 || northrend_inst.indexOf(mapId) > -1) {
                extention = 2;
            }

            if (maps_for_points.indexOf(mapId) > -1) {
                // Pass current map dimensions to get_player_position
                var pos = get_player_position(player.position_x, player.position_y, player.map, currentMapWidth, currentMapHeight);
                var pointHTML = '<div class="player-dot class-' + player.class + '" style="left:' + pos.x + 'px; top:' + pos.y + 'px;" onmousemove="tip(event, \'' + player.name + ' (' + player.level + ')\');" onmouseout="h_tip();"></div>';
                pointsHTML[extention] += pointHTML;
            }
        });

        document.getElementById('pointsOldworld').innerHTML = pointsHTML[0];
        document.getElementById('pointsOutland').innerHTML = pointsHTML[1];
        document.getElementById('pointsNorthrend').innerHTML = pointsHTML[2];
    }

    function tip(event, text) {
        var t = document.getElementById("tip");
        t.innerHTML = text;
        t.style.display = 'block';
        t.style.left = (event.clientX + 15) + 'px';
        t.style.top = (event.clientY - 10) + 'px';
    }

    function h_tip() {
        document.getElementById("tip").style.display = 'none';
    }

    function load_data() {
        console.log('Loading player data...');
        fetch('pomm_play.php')
            .then(response => response.json())
            .then(data => {
                console.log('Player data fetched:', data);
                show(data);
            })
            .catch(error => console.error('Error loading player data:', error));
    }

    function switchworld(id) {
        var maps = ['world', 'outland', 'northrend'];
        var buttons = document.getElementById('server_info').getElementsByTagName('span');
        
        for (var i = 0; i < maps.length; i++) {
            var mapDiv = document.getElementById(maps[i]);
            if (i === id) {
                mapDiv.style.visibility = 'visible';
                mapDiv.style.zIndex = 10;
                buttons[i].classList.add('active');
            } else {
                mapDiv.style.visibility = 'hidden';
                mapDiv.style.zIndex = 9 - i;
                buttons[i].classList.remove('active');
            }
        }
    }

    function init() {
        var serverInfoContainer = document.getElementById('server_info');
        maps_name_array.forEach((name, i) => {
            var mapElement = document.createElement('span');
            mapElement.innerText = name;
            mapElement.onclick = function() { switchworld(i); };
            serverInfoContainer.appendChild(mapElement);
        });
        switchworld(0);
        // resizeMap(); // This will be called by the parent window when the modal is shown
        load_data();
        setInterval(load_data, 15000);

        // Add resize event listener
        window.addEventListener('resize', resizeMap);
    }

    init();
</script>