<!doctype html>
<html><head><title>bwip-js - JavaScript Barcode Generator</title>
<meta charset="utf-8" />
<meta name="keywords" content="bar code, barcode generator, online barcode generator, free barcode generator, javascript, javascript library, nodejs, QR Code, EAN, EAN 13, Code 128, UPC, ISBN, ITF 14, Code 39, GS1, GS1 128, PDF417, HIBC, DataMatrix, Data Matrix, DataBar, OCR, OCR B, OCR font, Aztec Code, PostNet, USPS, Pharmacode" />
<meta name="description" content="JavaScript barcode generator and library.  Create any barcode in your browser." />
<link rel="stylesheet" type="text/css" href="lib/demo.css">
<script type="text/javascript" src="dist/bwip-js.js"></script>
<script type="text/javascript" src="lib/symdesc.js"></script>
<script type="text/javascript" src="lib/canvas-toblob.js"></script>
<script type="text/javascript" src="lib/filesaver.js"></script>
<script type="text/javascript" src="lib/inconsolata.js"></script>
<script type="text/javascript">
window.addEventListener('load', function() {
	var lastSymbol, lastBarText, lastAltText, lastOptions, lastRotate, lastScaleX, lastScaleY,
        lastRenderAs = 'render-canvas';
	try {
		lastSymbol	= localStorage.getItem('bwipjsLastSymbol');
		lastBarText	= localStorage.getItem('bwipjsLastBarText');
		lastAltText	= localStorage.getItem('bwipjsLastAltText');
		lastOptions = localStorage.getItem('bwipjsLastOptions');
		lastRotate	= localStorage.getItem('bwipjsLastRotate');
		lastScaleX  = +localStorage.getItem('bwipjsLastScaleX');
		lastScaleY  = +localStorage.getItem('bwipjsLastScaleY');
		lastRenderAs = localStorage.getItem('bwipjsLastRenderAs');
	} catch (e) {
	}

	// Set up the select list of barcode types
	var sel = document.getElementById('symbol');
	var opts = [];
	for (var id in symdesc) {
		opts.push(symdesc[id]);
	}
	opts.sort(function (a,b) { return a.desc < b.desc ? -1 : 1 });
	for (var i = 0, l = opts.length; i < l; i++) {
		var elt = document.createElement('option');
		elt.textContent = opts[i].desc;
		elt.value = opts[i].sym;
		sel.appendChild(elt);
	}

	sel.addEventListener('change', function(ev) {
			var desc = symdesc[sel.value];
			if (desc) {
				document.getElementById('symtext').value = desc.text;
				document.getElementById('symopts').value = desc.opts;
			} else {
				document.getElementById('symtext').value = '';
				document.getElementById('symopts').value = '';
			}
			document.getElementById('symaltx').value = '';
			document.getElementById('stats').textContent = '';
			document.getElementById('canvas').style.display = 'none';
			document.getElementById('svgdiv').style.display = 'none';
			document.getElementById('output').textContent = '';
		});

	if (lastSymbol) {
		sel.value = lastSymbol;
	} else {
		sel.selectedIndex = 0;
	}
	var evt = document.createEvent("HTMLEvents");
	evt.initEvent("change", false, true);
	sel.dispatchEvent(evt);

	if (lastBarText) {
		document.getElementById('symtext').value = lastBarText;
		document.getElementById('symaltx').value = lastAltText;
		document.getElementById('symopts').value = lastOptions;
	}
    if (lastRenderAs) {
        document.getElementById(lastRenderAs).checked = true;
    }
	if (lastScaleX && lastScaleY) {
		document.getElementById('scaleX').value = lastScaleX;
		document.getElementById('scaleY').value = lastScaleY;
	}
	if (lastRotate) {
		document.getElementById('rotate' + lastRotate).checked = true;
	}

	document.getElementById('scaleX').addEventListener('change', function(ev) {
			document.getElementById('scaleY').value = ev.target.value;
		});
	document.getElementById('render').addEventListener('click', render);

	// Allow Enter to render
	document.getElementById('params').addEventListener('keypress', function(ev) {
		if (ev.which == 13) {
			render();
			ev.stopPropagation();
			ev.preventDefault();
			return false;
		}
	});

	document.getElementById('versions').textContent =
				'bwip-js ' + bwipjs.BWIPJS_VERSION + ' / BWIPP ' + bwipjs.BWIPP_VERSION;

    // A reasonable match to OCR-B metrics.
    bwipjs.loadFont("Inconsolata", 95, 105, Inconsolata);
});

function render() {
	var elt  = symdesc[document.getElementById('symbol').value];
	var text = document.getElementById('symtext').value.trim();
	var alttext = document.getElementById('symaltx').value.trim();
	var options = document.getElementById('symopts').value.trim();
	var rotate = document.querySelector('input[name="rotate"]:checked').value;
	var scaleX = +document.getElementById('scaleX').value || 2;
	var scaleY = +document.getElementById('scaleY').value || 2;
    var renderAs = "render-canvas";
	try {
		localStorage.setItem('bwipjsLastSymbol',  elt.sym);
		localStorage.setItem('bwipjsLastBarText', text);
		localStorage.setItem('bwipjsLastAltText', alttext);
		localStorage.setItem('bwipjsLastOptions', options);
		localStorage.setItem('bwipjsLastScaleX', scaleX);
		localStorage.setItem('bwipjsLastScaleY', scaleY);
		localStorage.setItem('bwipjsLastRotate', rotate);
		localStorage.setItem('bwipjsLastRenderAs', renderAs);
	} catch (e) {
	}

	// Clear the page
	document.getElementById('output').value = '';
	document.getElementById('stats').value = '';
	document.getElementById('output').textContent = '';

	var canvas = document.getElementById('canvas');
	canvas.height = 1;
	canvas.width  = 1;
	canvas.style.display = 'none';

    var svgdiv = document.getElementById('svgdiv');
    svgdiv.style.display = 'none';

	// Convert the options to an object.
	let opts = {};
	let aopts = options.split(' ');
	for (let i = 0; i < aopts.length; i++) {
		if (!aopts[i]) {
			continue;
		}
		var eq = aopts[i].indexOf('=');
		if (eq == -1) {
			opts[aopts[i]] = true;
		} else {
			opts[aopts[i].substr(0, eq)] = aopts[i].substr(eq+1);
		}
	}

	// Finish up the options
	opts.text = text;
	opts.bcid = elt.sym;
	opts.scaleX = scaleX;
	opts.scaleY = scaleY;
	opts.rotate = rotate;
	if (alttext) {
		opts.alttext = alttext;
	}

    if (renderAs == 'render-canvas') {
        // Draw the bar code to the canvas
        try {
            let ts0 = new Date;
            bwipjs.toCanvas(canvas, opts);
            showCVS(ts0, new Date);
        } catch (e) {
            // Watch for BWIPP generated raiseerror's.
           
        }
    } else {
        // Draw the bar code as SVG
        try {
            let ts0 = new Date;

        } catch (e) {
            // Watch for BWIPP generated raiseerror's.
            var msg = (''+e).trim();
            if (msg.indexOf("bwipp.") >= 0) {
                document.getElementById('output').textContent = msg;
            } else if (e.stack) {
                // GC includes the message in the stack.  FF does not.
                // GC includes the message in the stack.  FF does not.
                document.getElementById('output').textContent = 
                        (e.stack.indexOf(msg) == -1 ? msg + '\n' : '') + e.stack;
            } else {
                document.getElementById('output').textContent = msg;
            }
            return;
        }
    }

	function showCVS(ts0, ts1) {
        canvas.style.display = '';
		setURL();
		document.getElementById('stats').textContent = elt.sym + ' rendered in ' + (ts1-ts0) + ' msecs.';
		saveCanvas.basename = elt.sym + '-' + text.replace(/[^a-zA-Z0-9._]+/g, '-');
        if (window.devicePixelRatio) {
            canvas.style.zoom = 1 / window.devicePixelRatio;
        } else {
            canvas.style.zoom = 1;
        }
	}


}


</script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<style>
    .navbar h3 {
        text-decoration: underline;
        font-family: Helvetica;
        font-weight: 600;
        color: rgb(16, 16, 16);
        font-size: 25px;
        text-align: center;
    }
    .navbar ul {
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        list-style: none; /* Remove the bullets */
    }
    .navbar .optionss{
        text-align: center;
    }
</style>


<body>
	<nav class="navbar navbar-default">
		<h3>Web Systems and Technologies</h3>
		<div class = "optionss " style ="margin:20px">
		
			<tr><th>Barcode Type:<td><select id="symbol" style="width: 150px; height: 30px; margin-left:10px; margin-right: 30px;"></select>
			<tr><th>Alt Text:<td><input id="symaltx" type="text" spellcheck="false" style="width: 150px; height: 30px; margin-left:10px; margin-right: 30px;">
			<tr><th>Options:<td><input id="symopts" type="text" spellcheck="false" style="width: 150px; height: 30px margin-left:10px;">
			<tr><td><td>
				<div id="stats"></div>

		</div>
		<div class = "optionss " style ="margin-top:20px; margin-bottom: 10px;">
		
			
				<label for="render-canvas" id="render-canvas"></label>

			<tr><th>Image Size (<i>width, height</i>):<td>
				<input type="number" min=1 max=9 step=1 id="scaleX" value=2>
				<input style="margin-right: 30px;" type="number" min=1 max=9 step=1 id="scaleY" value=2>
			<tr><th>Image Rotation:<td>
				<label for="rotateN"><input type="radio" name="rotate" value="N"
					id="rotateN" checked>Normal</label>
				<label for="rotateR"><input type="radio" name="rotate" value="R"
					id="rotateR">Right</label>
				<label for="rotateL"><input type="radio" name="rotate" value="L"
					id="rotateL">Left</label>
				<label style="margin-right: 30px;"for="rotateI"><input type="radio" name="rotate" value="I"
					id="rotateI">Invert</label>

		</div>
		
	
	</nav>


	<div class="container col-md-6 col-md-offset-3 well">
		<h3 class="text-primary text-center">Code Generator</h3>
	  
		<form name="form">
		  <p>
			<label for="txt">Enter a text</label>
			<textarea name="text" cols="60" rows="6" title="Type barcode text here" onkeyup="svg.style.display = 'none'" id="symtext" type="text" spellcheck="false" style="width: 100%; height: 30px;"></textarea>
		  </p>
		  <div class="form-group text-center">
			<button class="btn btn-primary" style="margin-top:1ex" id="render" type="button" onclick="generateBarcode()">Show Barcode</button>
		  </div>
		</form>
		<center>
			<div id="content">
				<canvas id="canvas" width=10 height=10 style="display:none"></canvas>
				<div id="svgdiv" style="display:none"></div>
				<div id="output" style="white-space:pre; font-size: 10px;"></div>
			</div>

		</center>



	  </div>
	</br>



<div id="params">

<td style="padding-left:10mm;vertical-align:top">


</div>

</body>
</html>
