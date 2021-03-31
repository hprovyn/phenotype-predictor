var hairCoeff = {'rs312262906':	[1.2522, 25.508, 2.732],
'rs11547464':	[-0.61155,	2.5381,	-16.969],
'rs885479':	[0.2937,	-0.20889,	0.39983],
'rs1805008': [-0.50143,	2.801,	-0.86062],
'rs1805005': [	0.21172,	0.93493,	-0.0029013],
'rs1805006': [	1.93E+00,	3.65E+00,	-1.61E+01],
'rs1805007': [	-0.32318,	3.4408,	-1.3757],
'rs1805009': [	0.60861,	4.5868,	0.060631],
'rs201326893': [	0.25624,	22.107,	3.9824],
'rs2228479': [	-0.054143,	0.62307,	0.17012],
'rs1110400': [	-0.56315,	1.4453,	0.29143],
'rs28777': [	0.52168,	0.70401,	0.82228],
'rs16891982': [	0.75284,	-0.41869,	1.1617],
'rs12821256': [	-0.34957,	-0.57964,	-0.89824],
'rs4959270': [	-0.19171,	0.24861,	-0.36359],
'rs12203592': [	1.6475,	0.90233,	1.997],
'rs1042602': [	0.16092,	0.45003,	0.065432],
'rs1800407': [	-0.19111,	-0.27606,	-0.49601],
'rs2402130': [	0.35821,	0.28313,	0.26536],
'rs12913832': [	1.214,	-0.093776,	1.9391],
'rs2378249': [	0.12669,	0.76634,	-0.089509],
'rs683': [	0.21172,	-0.053427,	0.15796]
}

var hairConst = [-2.0769,	-6.3953,	-2.4029]
var hairPhenos = ["Brown","Red","Black","Blonde"]

var shadeCoeff = {'rs11547464': [	-16.575],
    'rs885479': [	0.18709],
    'rs1805008': [	-0.9331],
    'rs1805005': [	-0.030452],
    'rs1805006': [	-15.305],
    'rs1805007': [	-1.7901],
    'rs1805009': [	-0.078426],
    'rs2228479': [	0.0049399],
    'rs1110400': [	-0.15892],
    'rs28777': [	1.4594],
    'rs16891982': [	0.78071],
    'rs12821256': [	-0.7757],
    'rs4959270': [	-0.44286],
    'rs12203592': [	1.8636],
    'rs1042602': [	0.18179],
    'rs1800407': [	-0.59583],
    'rs2402130': [	0.33304],
    'rs12913832': [	1.9622],
    'rs2378249': [	0.090083],
    'rs683': [	0.18114]
    }

var shadeConst = [-2.528]
var shadePhenos = ["Dark", "Light"]

var eyeCoeff = {'rs12913832': [	-4.87,	-1.99],
    'rs1800407': [	1.15,	1.05],
    'rs12896399': [	-0.53,	-0.01],
    'rs16891982': [	-1.53,	-0.74],
    'rs1393350': [	0.44,	0.26],
    'rs12203592': [	0.60,	0.69]
}

var eyeConst = [3.84,	0.37]

var eyePhenos = ["Blue","Intermediate","Brown"]

var testAlleles = {'rs312262906	':	2,
'rs11547464':		2,
'rs885479':		2,
'rs1805008':		2,
'rs1805005':		2,
'rs1805006':		2,
'rs1805007':		2,
'rs1805009':		1,
'rs201326893':		2,
'rs2228479':		2,
'rs1110400':		2,
'rs16891982':		2,
'rs12821256':		2,
'rs4959270':		1,
'rs12203592':		0,
'rs1042602':		1,
'rs1800407':		0,
'rs2402130':		1,
'rs12913832':		0,
'rs2378249':		1,
'rs12896399':		2,
'rs1393350':		0,
'rs683':		1,
}

function adjustWeightsCorrectBlonde(coeffs, constants) {
    var theCoeffs = Object.assign({}, coeffs)
    var theConsts = Object.assign({}, constants)
    theCoeffs['rs1805005'] = coeffs['rs1805005'].map(x => x * 1.5)
    theCoeffs['rs4959270'] = coeffs['rs4959270'].map(x => x / 3)
    theCoeffs['rs1042602'] = coeffs['rs1042602'].map(x => x / 3)
    theConsts[0] += 1
    return [theCoeffs, theConsts]
}

function predictEyeHairShade(alleles, coeffs, constants, phenotypes) {
    var phenosless1 = Object.entries(coeffs)[0][1].length
    var sums = new Array(phenosless1).fill(0)
    var addedMsg = ""
    for (const [key, value] of Object.entries(alleles)) {
        for (var i = 0; i < phenosless1; i++) {
            if (key in coeffs) {

                sums[i] += value * coeffs[key][i]
                if (i == 0 ) {
                    addedMsg += key + " ";
                }
            }     
        }
    }
    var exps = new Array(phenosless1).fill(0)
    for (var i = 0; i < phenosless1; i++) {
        exps[i] = Math.exp(constants[i] + sums[i])
    }
    var probs = Array(phenosless1).fill(0)
    var sum = exps.reduce((a, b) => a + b, 0)
    var returnObj = {}
    for (var i = 0; i < phenosless1; i++) {
        probs[i] = exps[i] / (1 + sum)
        returnObj[phenotypes[i]] = probs[i]
    }
    returnObj[phenotypes[phenotypes.length - 1]] = 1 - probs.reduce((a, b) => a + b, 0)
    return returnObj
}

function predictCurly(alleles) {
    var curlyDelta = 0 //pos curly, neg straight
    var hasAny = false
    if (alleles.hasOwnProperty('rs11803731')) {
        var count = alleles['rs11803731']
        if (count == 0) {
            curlyDelta -= .24
            hasAny = true
        }
        if (count == 2) {
            curlyDelta += .24
            hasAny = true
        }
    }
    if (alleles.hasOwnProperty('rs17646946')) {
        var count = alleles['rs17646946']
        curlyDelta -= .29 * count
        if (count > 0) {
            hasAny = true
        }
    }
    if (alleles.hasOwnProperty('rs7349332')) {
        var count = alleles['rs7349332']
        curlyDelta += .19 * count
        if (count > 0) {
            hasAny = true
        }
    }
    
    var curlyProb = .50 + curlyDelta *.5 / .72
    return [hasAny, Math.max(0, curlyProb)]
}

function getAllPredictions(alleles) {
    var returnObj = {}
    var modified = adjustWeightsCorrectBlonde(hairCoeff, hairConst)
    returnObj["eye"] = predictEyeHairShade(alleles, eyeCoeff, eyeConst, eyePhenos)
    returnObj["hair_color"] =  predictEyeHairShade(alleles, modified[0], modified[1], hairPhenos)
    returnObj["hair_shade"] =  predictEyeHairShade(alleles, shadeCoeff, shadeConst, shadePhenos)
    return returnObj
}

function getRandomSumToOne(phenotypeNames) {
    var phenoProbs = {}
    var sum = 0
    for (var i = 0; i < phenotypeNames.length; i++) {
        var thisProb = Math.random()
        phenoProbs[phenotypeNames[i]] = thisProb
        sum += thisProb
    }
    for (var i = 0; i < phenotypeNames.length; i++) {
        phenoProbs[phenotypeNames[i]] = phenoProbs[phenotypeNames[i]] / sum
    }
    return phenoProbs

}
function getRandomAvatarURL() {
    var skinFrecklePredictions = getRandomSumToOne(["Non-freckled skin","Moderate freckling","Severe freckling"])
    var hairColorPreds = getRandomSumToOne(["Brown","Red","Blonde","Black"])
    var shadePreds = getRandomSumToOne(["Dark","Light"])
    var skinPreds = getRandomSumToOne(["Light/pale","Dark/olive","Moderate"])
    var eyePreds = getRandomSumToOne(["Blue","Intermediate","Brown"])
    var curlyPreds = getRandomSumToOne(["Curly","Straight"])
    var baldPreds = {"Balding": Math.random() * Math.random() * Math.random()}
    var sex = "M"
    
    if (Math.random() > 0.5) {
        sex = "F"
    }
    
    alleles = {'rs12913832': 'something'}
    return getAvatarURL({"skin color": skinPreds, "freckling": skinFrecklePredictions}, {"eye": eyePreds, "hair_color": hairColorPreds, "hair_shade": shadePreds}, curlyPreds, baldPreds, sex)
}

function getAvatarURL(skinFrecklePredictions, eyeHairShadePredictions, curlyPreds, baldingPreds, sex) {
    var eyePreds = eyeHairShadePredictions["eye"]
    var hairPreds = eyeHairShadePredictions["hair_color"]
    var shadePreds = eyeHairShadePredictions["hair_shade"]
    var skinPreds = skinFrecklePredictions["skin color"]
    var frecklePreds = skinFrecklePredictions["freckling"]

    var maxEye = Object.keys(eyePreds)[Object.values(eyePreds).indexOf(Math.max(...Object.values(eyePreds)))]
    var maxHair = Object.keys(hairPreds)[Object.values(hairPreds).indexOf(Math.max(...Object.values(hairPreds)))]
    var maxShade = Object.keys(shadePreds)[Object.values(shadePreds).indexOf(Math.max(...Object.values(shadePreds)))]
    var maxCurly = Object.keys(curlyPreds)[Object.values(curlyPreds).indexOf(Math.max(...Object.values(curlyPreds)))]
    var maxSkin = Object.keys(skinPreds)[Object.values(skinPreds).indexOf(Math.max(...Object.values(skinPreds)))]
    var maxFreckle = Object.keys(frecklePreds)[Object.values(frecklePreds).indexOf(Math.max(...Object.values(frecklePreds)))]
    var hairColor = maxHair
    
    if (maxShade == "Dark") {
        if (maxHair == "Brown") {
            hairColor = "BrownDark"
        }
    } else {
        if (maxHair == "Blonde" && shadePreds["Light"] > .75) {
            hairColor = "Platinum"
        }
    }
    if (maxHair == "Brown" || maxHair == "Red") {
        var brownRedRatio = hairPreds["Brown"] / hairPreds["Red"]
        if (brownRedRatio < 13/10 && brownRedRatio > 10/13) {
            hairColor = "Auburn"
        }
    }
    var topType = null    

    if (curlyPreds["Curly"] > .75) {
        if (sex == "F") {
            topType = "LongHairCurly"    
        } else {
            topType = "ShortHairShortCurly"
        }
    }
    if (curlyPreds["Curly"] <= .75 && curlyPreds["Curly"] > .5) {
        if (sex == "F") {
            topType = "LongHairCurvy"
        } else {
            topType = "ShortHairShortWaved"    
        }
    }
    if (curlyPreds["Curly"] <= .5 && curlyPreds["Curly"] > .25) {
        if (sex == "F") {
            topType = "LongHairBigHair"
        } else {
            topType = "ShortHairShortRound"    
        }
    }
    if (curlyPreds["Curly"] <= 0.25) {
        if (sex == "F") {
            topType = "LongHairStraight2"
        } else {
            topType = "ShortHairShortFlat"    
        }
    }
    if (sex == "M" && baldingPreds["Balding"] > 0.5) {
        topType = "ShortHairSides"
    }
    var lightPale = skinPreds["Light/pale"]
    var darkOlive = skinPreds["Dark/olive"]
    var moderate = skinPreds["Moderate"]
    var darkToLightRatio = (darkOlive * 2 + moderate) / (moderate + lightPale * 2)
    if (lightPale > 0.65 || darkToLightRatio < 0.7) {
        skin = "Pale"
    } else {
        if (darkOlive > 0.8 || darkToLightRatio > 7) {
            skin = "Black"
        } else {
            if (darkOlive > 0.65 || darkToLightRatio > 2.5) {
                skin = "DarkBrown"
            } else {
                if ((maxSkin == "Dark/olive" || maxSkin == "Moderate") && darkToLightRatio > 1.8) {
                    skin = "Brown"
                } else {
                    skin = "Light"
                }
            }
        }
    }

    var eyeType = "Brown"
    eyeType = maxEye
    
    var freckleType = "Default"
    var noFreckles = frecklePreds["Non-freckled skin"]
    var moderateFreckling = frecklePreds["Moderate freckling"]
    var severeFreckling = frecklePreds["Severe freckling"]
    if (maxFreckle == "Severe freckling") {
        freckleType = "Severe"
    } else {
        if (moderateFreckling > 1.3 * noFreckles) {
            freckleType = "Moderate"
        } else {
            if (noFreckles > 1.3 * moderateFreckling) {
                freckleType = "Light"
            } else {
                freckleType = "Default"
            }
        }
    }

    var baseURL = 'https://predict.yseq.net/customerAvatars/dev-build/index.html?'
    //return baseURL + skin + "_" + hairColor + "_" + topType + ".png"
    var eyeOrGlassesArgs = "eyeType=" + eyeType
    if (alleles.hasOwnProperty('rs12913832') == false) {
        eyeOrGlassesArgs = "accessoriesType=Sunglasses"
    }
    var eyelashType = "Default"
    if (sex == "F") {
        eyelashType = "FemaleLashes"
    }
    return baseURL + "skinColor=" + skin + "&hairColor=" + hairColor + "&topType=" + topType + "&" + eyeOrGlassesArgs + "&freckleType=" + freckleType + "&eyelashType=" + eyelashType
}

function addAvatarToDiv(url, tag, height, width) {
    var div = document.getElementById(tag)
    var elem = document.createElement("iframe")
    elem.src = url;
    elem.setAttribute("height", height);
    elem.setAttribute("scrolling", "no");
    elem.setAttribute("width", width);
    elem.setAttribute("alt", "avatar");
    elem.style.border = 0
    div.appendChild(elem);
}

function addTableToDivOld(tag, phenotypeProps) {
    var html = "<table align='center' border='1'><tr><td>Phenotype</td><td>Probability</td></tr>";
    for (const [pheno, prob] of Object.entries(phenotypeProps)) {
        html += "<tr><td>" + pheno + "</td><td>" + prob.toFixed(2) + "</td></tr>";
    }
    html += "</table>";
    var div = document.getElementById(tag);
    div.innerHTML = html;
}

var tagToPhenoColorsAndCodes = {"hair_color": {'Brown': {'char': "&#9608;", 'color':'#724133'},
                                               'Red': {'char': "&#9608;", 'color':'#C93305'},
                                               'Black': {'char': "&#9608;", 'color':'#2C1B18'},
                                               'Blonde': {'char': "&#9608;", 'color':'#B58143'}},
                                "hair_shade": {'Light': {'char': "&#9608;", 'color':'#FFFDD0'},
                                               'Dark': {'char':"&#9608;", 'color':'#2C1B18'}},
                                "eye": {'Blue': {'char':"&#9608;", 'color':'#4287F5'},
                                        'Brown': {'char':"&#9608;", 'color':'#964B00'},
                                        'Intermediate': {'char':"&#9608;", 'color':'#00A300/#C46200'}},
                                "skin_color": {'Light/pale': {'char':"&#9608;", 'color':'#FFDBB4'},
                                               'Dark/olive': {'char':"&#9608;", 'color':'#AE5D29/#614335'},
                                        'Moderate': {'char':"&#9608;", 'color':'#EDB98A/#D08B5B'}},
                                "curliness": {'Curly': {'char': '~', 'color': '#000000'},
                                              'Straight': {'char': '-', 'color': '#000000'}},
                                "freckling": {'Severe freckling': {'char':":", 'color':'#4F372B'},
                                              'Moderate freckling': {'char':".", 'color':'#4F372B'},
                                              'Non-freckled skin': {'char':"&#9608;", 'color':'#FFFDD0'}},
                                "sunburn_tanning": {'High susceptibility to sunburns': {'char':'', 'color':'#F03737'},
                                                    'Initial sunburns': {'char':'', 'color':'#F57D7D'},
                                                    'Moderate tanning': {'char':'', 'color':'#F28416'},
                                                    'Quick tanning': {'char':'', 'color':'#B55E07'}}                            
                            }

                                
function addTableToDiv(tag, phenotypeProps) {
    var i = 0
    var html = "<div style='overflow-x: auto; width='80%'> <table align='center' border='1' style='font-family: monospace;table-layout: fixed;width: 80%'> <col style='width: 40%;' /> <col style='width: 20%;' /><col style='width: 40%;' /><tr><td>Phenotype</td><td>Probability</td><td><img src='images/bar_scale.png'></img></td></tr>";
    for (const [pheno, prob] of Object.entries(phenotypeProps)) {
        html += "<tr><td>" + pheno + "</td><td>" + Math.round(prob * 1000) / 10 + "%</td><td>"
        var colorSplit = tagToPhenoColorsAndCodes[tag][pheno]['color'].split("/")
        var code = tagToPhenoColorsAndCodes[tag][pheno]['char']
        var theColor = colorSplit[0]
        
        if (colorSplit.length > 1) {
            html += '<svg width="205" height="12"><defs><linearGradient id="' + tag + '_gradient_' + pheno + '" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" style="stop-color:' + colorSplit[0] + ';stop-opacity:1" /><stop offset="100%" style="stop-color:' + colorSplit[1] + ';stop-opacity:1" /></linearGradient></defs><rect width="' + prob * 200 + '" height="10" style="fill:url(#' + tag + '_gradient_' + pheno + ');stroke-width:1;stroke:rgb(0,0,0)" /></svg>'
        } else {
            html += '<svg width="205" height="12"><rect width="' + prob * 200 + '" height="10" style="fill:' + theColor + ';stroke-width:1;stroke:rgb(0,0,0)" /></svg>'    
        }
        
        html += "</td></tr>"
    }
    html += "</table></div>"
    var div = document.getElementById(tag);
    div.innerHTML = html;
}