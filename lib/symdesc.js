// file: bwip-js/lib/symdesc.js
//
// This code was automatically generated from:
// Barcode Writer in Pure PostScript - Version 2023-04-03
//
// Copyright (c) 2011-2023 Mark Warren
// Copyright (c) 2004-2023 Terry Burton
//
// Licensed MIT.  See the LICENSE file in the bwip-js root directory
// for the extended copyright notice.
var symdesc = {
	"ultracode":{ sym:"ultracode",desc:"Ultracode",text:"Ultracode Sample",opts:"eclevel=EC2" },
	"datamatrix":{ sym:"datamatrix",desc:"Data Matrix",text:"DataMatrix Sample",opts:"" },
	"code128":{ sym:"code128",desc:"Code 128",text:"1234567890",opts:"includetext" },
	"pdf417":{ sym:"pdf417",desc:"PDF417",text:"PDF417 Sample",opts:"columns=2" },
	"isbn":{ sym:"isbn",desc:"ISBN",text:"978-1-56581-231-4 90000",opts:"includetext guardwhitespace" },
	"rationalizedCodabar":{ sym:"rationalizedCodabar",desc:"Codabar",text:"A0123456789B",opts:"includetext includecheck includecheckintext" },
	"code39":{ sym:"code39",desc:"Code 39",text:"1234567890",opts:"includetext includecheck includecheckintext" },
	"qrcode":{ sym:"qrcode",desc:"QR Code",text:"http://google.com",opts:"eclevel=M" },
	"azteccode":{ sym:"azteccode",desc:"Aztec Code",text:"Aztec Code Sample",opts:"format=full" },
	"maxicode":{ sym:"maxicode",desc:"MaxiCode",text:"[)>^03001^02996152382802^029840^029001^0291Z00004951^029UPSN^02906X610^029159^0291234567^0291/1^029^029Y^029634 ALPHA DR^029PITTSBURGH^029PA^029^004",opts:"mode=2 parse" }

};

if (typeof module == 'object' && typeof module.exports == 'object') {
  module.exports = symdesc;
}
