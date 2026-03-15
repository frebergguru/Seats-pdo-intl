<?php
require '../includes/config.php';
require '../includes/functions.php';
require '../includes/i18n.php';

$pdo = requireAdmin();
$baseUrl = '../';
noCacheHeaders();

// Handle download as file (before any output)
if (isset($_GET['download']) && $_GET['download'] === '1') {
    $dlData = getMapData($pdo);
    $dlMap = implode("\n", $dlData['grid']);
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="map.txt"');
    header('Content-Length: ' . strlen($dlMap));
    echo $dlMap;
    exit();
}

// Handle POST actions then redirect (PRG pattern)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfOk = isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);

    if (!$csrfOk) {
        setFlash('error', $langArray['invalid_csrf_token']);
    } elseif (isset($_POST['map_data'])) {
        $lines = preg_split('/\r\n|\r|\n/', $_POST['map_data']);
        $cleanLines = [];
        foreach ($lines as $line) {
            $cleanLine = preg_replace('/[^#wfdbke]/', '', $line);
            if (strlen($cleanLine) > 0) $cleanLines[] = $cleanLine;
        }
        $cleanMap = implode("\n", $cleanLines);
        if (!empty($cleanMap)) {
            try {
                saveMapData($pdo, $cleanMap);
                setFlash('success', $langArray['admin_map_saved']);
            } catch (PDOException $e) {
                error_log("Admin map save error: " . $e->getMessage());
                setFlash('error', $langArray['error_occurred']);
            }
        }
    } elseif (isset($_POST['import_upload'])) {
        if (isset($_FILES['map_file']) && $_FILES['map_file']['error'] === UPLOAD_ERR_OK) {
            $lines = preg_split('/\r\n|\r|\n/', trim(file_get_contents($_FILES['map_file']['tmp_name'])));
            $cleanLines = [];
            foreach ($lines as $line) {
                $cleanLine = preg_replace('/[^#wfdbke]/', '', $line);
                if (strlen($cleanLine) > 0) $cleanLines[] = $cleanLine;
            }
            $cleanMap = implode("\n", $cleanLines);
            if (!empty($cleanMap)) {
                try {
                    saveMapData($pdo, $cleanMap);
                    setFlash('success', $langArray['admin_map_imported']);
                } catch (PDOException $e) {
                    setFlash('error', $langArray['error_occurred']);
                }
            } else {
                setFlash('error', $langArray['admin_map_import_invalid']);
            }
        } else {
            setFlash('error', $langArray['admin_map_import_no_file']);
        }
    }

    header("Location: map.php");
    exit();
}

// GET: generate fresh CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$csrfToken = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');

$mapData = getMapData($pdo);
$currentMap = implode("\n", $mapData['grid']);
$gridRows = count($mapData['grid']);
$gridCols = $gridRows > 0 ? strlen($mapData['grid'][0]) : 10;

require '../includes/header.php';
renderAdminNav('map');
?>

<div class="admin-page-title"><?php echo $langArray['admin_map_editor']; ?></div>

<?php echo getFlash(); ?>

<!-- Tile palette -->
<div class="admin-panel" style="max-width:700px;">
    <div class="admin-panel-header"><?php echo $langArray['symbol_explanation']; ?></div>
    <div class="admin-panel-body">
        <div class="map-palette" id="mapPalette">
            <button type="button" class="map-palette-btn active" data-tile="#">
                <span class="swatch" style="background:#4CAF50;"></span> <?php echo $langArray['vacant_seat']; ?>
            </button>
            <button type="button" class="map-palette-btn" data-tile="w">
                <span class="swatch" style="background:#666;"></span> <?php echo $langArray['wall']; ?>
            </button>
            <button type="button" class="map-palette-btn" data-tile="f">
                <span class="swatch" style="background:#2a2a2a; border:1px solid #555;"></span> <?php echo $langArray['floor']; ?>
            </button>
            <button type="button" class="map-palette-btn" data-tile="d">
                <span class="swatch" style="background:#8B4513;"></span> <?php echo $langArray['door']; ?>
            </button>
            <button type="button" class="map-palette-btn" data-tile="e">
                <span class="swatch" style="background:#d9534f;"></span> <?php echo $langArray['exit']; ?>
            </button>
            <button type="button" class="map-palette-btn" data-tile="k">
                <span class="swatch" style="background:#FF9800;"></span> <?php echo $langArray['kitchen']; ?>
            </button>
            <button type="button" class="map-palette-btn" data-tile="b">
                <span class="swatch" style="background:#2196F3;"></span> <?php echo $langArray['bathroom']; ?>
            </button>
        </div>
    </div>
</div>

<!-- Grid size controls -->
<div class="admin-panel" style="max-width:700px;">
    <div class="admin-panel-header">Grid Size</div>
    <div class="admin-panel-body">
        <div class="map-size-controls">
            <label>Rows: <input type="number" id="gridRows" value="<?php echo (int)$gridRows; ?>" min="1" max="50"></label>
            <label>Cols: <input type="number" id="gridCols" value="<?php echo (int)$gridCols; ?>" min="1" max="50"></label>
            <button type="button" class="admin-btn" id="resizeGrid">Resize</button>
        </div>
    </div>
</div>

<!-- Interactive grid editor -->
<div class="admin-panel" style="max-width:900px;">
    <div class="admin-panel-header"><?php echo $langArray['admin_map_editor']; ?></div>
    <div class="admin-panel-body" style="overflow-x:auto;">
        <div class="map-editor-grid" id="mapGrid"></div>
    </div>
</div>

<!-- Save form with hidden textarea -->
<form method="POST" action="map.php" id="mapForm">
    <textarea name="map_data" id="mapDataField" class="map-editor-text" style="display:none;"><?php echo htmlspecialchars($currentMap, ENT_QUOTES, 'UTF-8'); ?></textarea>
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <div style="text-align:center; margin:15px 0;">
        <button type="submit" class="admin-btn admin-btn-lg"><?php echo $langArray['admin_save']; ?></button>
        <button type="button" class="admin-btn admin-btn-secondary admin-btn-lg" id="toggleTextMode">Text Mode</button>
    </div>
</form>

<div style="text-align:center; margin-bottom:20px; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
    <a href="map.php?download=1" class="admin-btn admin-btn-secondary"><?php echo $langArray['admin_download_map']; ?></a>
    <button type="button" class="admin-btn admin-btn-secondary" id="showImportFile"><?php echo $langArray['admin_import_file']; ?></button>
    <button type="button" class="admin-btn admin-btn-secondary" id="showImportText"><?php echo $langArray['admin_import_text']; ?></button>
</div>

<!-- File upload import -->
<div class="admin-panel" style="max-width:700px; display:none;" id="importFilePanel">
    <div class="admin-panel-header"><?php echo $langArray['admin_import_file']; ?></div>
    <div class="admin-panel-body">
        <form method="POST" action="map.php" enctype="multipart/form-data">
            <div class="admin-form-group">
                <label for="map_file"><?php echo $langArray['admin_import_file_label']; ?></label>
                <input type="file" name="map_file" id="map_file" accept=".txt,.text">
            </div>
            <input type="hidden" name="import_upload" value="1">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <div class="admin-form-actions">
                <button type="submit" class="admin-btn"><?php echo $langArray['admin_import_btn']; ?></button>
                <button type="button" class="admin-btn admin-btn-secondary" onclick="$('#importFilePanel').hide();"><?php echo $langArray['cancel']; ?></button>
            </div>
        </form>
    </div>
</div>

<!-- Text paste import -->
<div class="admin-panel" style="max-width:700px; display:none;" id="importTextPanel">
    <div class="admin-panel-header"><?php echo $langArray['admin_import_text']; ?></div>
    <div class="admin-panel-body">
        <div class="admin-form-group">
            <label for="importTextarea"><?php echo $langArray['admin_import_text_label']; ?></label>
            <textarea id="importTextarea" class="map-editor-text" rows="10" placeholder="wwwww&#10;w###w&#10;wwwww"></textarea>
        </div>
        <div class="admin-form-actions">
            <button type="button" class="admin-btn" id="applyImportText"><?php echo $langArray['admin_import_btn']; ?></button>
            <button type="button" class="admin-btn admin-btn-secondary" onclick="$('#importTextPanel').hide();"><?php echo $langArray['cancel']; ?></button>
        </div>
    </div>
</div>

<script>
$(function() {
    var tileClasses = { '#':'map-cell-seat','w':'map-cell-wall','f':'map-cell-floor','d':'map-cell-door','e':'map-cell-exit','k':'map-cell-kitchen','b':'map-cell-bathroom' };
    var tileLabels = { '#':'#','w':'','f':'','d':'\uD83D\uDEAA','e':'E','k':'K','b':'B' };
    var currentTile = '#', grid = [], isMouseDown = false, textMode = false;

    function parseMap() {
        var text = $('#mapDataField').val().trim();
        grid = [];
        if (text) { text.split('\n').forEach(function(l){ grid.push(l.split('')); }); }
        if (!grid.length) grid = [['f','f','f','f','f'],['f','#','#','#','f'],['f','f','f','f','f']];
        $('#gridRows').val(grid.length);
        $('#gridCols').val(grid[0] ? grid[0].length : 5);
    }

    function renderGrid() {
        var $g = $('#mapGrid').empty();
        if (!grid.length) return;
        $g.css('grid-template-columns','repeat('+grid[0].length+',28px)');
        for (var r=0;r<grid.length;r++)
            for (var c=0;c<grid[r].length;c++) {
                var t=grid[r][c]||'f';
                $g.append('<div class="map-editor-cell '+(tileClasses[t]||'map-cell-floor')+'" data-r="'+r+'" data-c="'+c+'">'+(tileLabels[t]||'')+'</div>');
            }
    }

    function syncTextarea() {
        var lines=[];
        for(var r=0;r<grid.length;r++) lines.push(grid[r].join(''));
        $('#mapDataField').val(lines.join('\n'));
    }

    function paintCell(r,c) {
        if(r<0||r>=grid.length||c<0||c>=grid[0].length) return;
        grid[r][c]=currentTile;
        var $c=$('#mapGrid .map-editor-cell[data-r="'+r+'"][data-c="'+c+'"]');
        $c.attr('class','map-editor-cell '+(tileClasses[currentTile]||'map-cell-floor')).text(tileLabels[currentTile]||'');
        syncTextarea();
    }

    $('#mapPalette').on('click','.map-palette-btn',function(){ $('#mapPalette .map-palette-btn').removeClass('active'); $(this).addClass('active'); currentTile=$(this).data('tile'); });

    $('#mapGrid').on('mousedown','.map-editor-cell',function(e){ e.preventDefault(); isMouseDown=true; paintCell($(this).data('r'),$(this).data('c')); });
    $('#mapGrid').on('mouseenter','.map-editor-cell',function(){ if(isMouseDown) paintCell($(this).data('r'),$(this).data('c')); });
    $(document).on('mouseup',function(){ isMouseDown=false; });

    $('#mapGrid').on('touchstart','.map-editor-cell',function(e){ e.preventDefault(); isMouseDown=true; paintCell($(this).data('r'),$(this).data('c')); });
    $('#mapGrid').on('touchmove',function(e){ e.preventDefault(); var t=e.originalEvent.touches[0],el=document.elementFromPoint(t.clientX,t.clientY); if(el&&$(el).hasClass('map-editor-cell')) paintCell($(el).data('r'),$(el).data('c')); });
    $('#mapGrid').on('touchend',function(){ isMouseDown=false; });

    $('#resizeGrid').on('click',function(){
        var nr=Math.max(1,Math.min(50,parseInt($('#gridRows').val())||1)),nc=Math.max(1,Math.min(50,parseInt($('#gridCols').val())||1)),ng=[];
        for(var r=0;r<nr;r++){var row=[];for(var c=0;c<nc;c++) row.push((grid[r]&&grid[r][c])?grid[r][c]:'f'); ng.push(row);}
        grid=ng; syncTextarea(); renderGrid();
    });

    $('#toggleTextMode').on('click',function(){
        textMode=!textMode;
        if(textMode){ syncTextarea(); $('#mapDataField').show(); $('#mapGrid').closest('.admin-panel').hide(); $(this).text('Visual Mode'); }
        else { parseMap(); renderGrid(); $('#mapDataField').hide(); $('#mapGrid').closest('.admin-panel').show(); $(this).text('Text Mode'); }
    });

    $('#showImportFile').on('click',function(){ $('#importTextPanel').hide(); $('#importFilePanel').toggle(); });
    $('#showImportText').on('click',function(){ $('#importFilePanel').hide(); $('#importTextPanel').toggle(); });

    $('#applyImportText').on('click',function(){
        var text=$('#importTextarea').val().trim();
        if(text){ $('#mapDataField').val(text); parseMap(); renderGrid(); $('#importTextPanel').hide(); $('#importTextarea').val(''); }
    });

    $('#mapForm').on('submit',function(){ if(!textMode) syncTextarea(); });

    parseMap();
    renderGrid();
});
</script>

<?php require '../includes/footer.php'; ?>
