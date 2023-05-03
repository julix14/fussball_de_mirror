const fs = require('fs');
const axios = require('axios');
const dotenv = require("dotenv")
const {Client} = require("pg")
const format = require('pg-format');


dotenv.config()
// Get wam_base.json

const pathToWam = 'data/wam_base.json';

if (!fs.existsSync(pathToWam)) {
    console.log('wam_base.json does not exist');
    getWam();
    console.log('wam_base.json loaded');
} else {
    console.log('wam_base.json exists');
}
//saveInDatabase();
//getKindFiles();

doDBStuff().then(r => console.log('done'));

async function doDBStuff(){
    const client = await connectDb();
    saveRegionsFromFiles('data/kinds', client);
}
async function getWam() {
    axios.get('https://www.fussball.de/wam_base.json')
        .then(response => {
            fs.writeFile(pathToWam, JSON.stringify(response.data, null, "\t"), (err) => {
                if (err) throw err;
                console.log('wam_base.json saved');
            });
        })
}

async function loadKinds() {
    let wam = undefined;
    try {
        wam = JSON.parse(fs.readFileSync(pathToWam, 'utf8'));
    } catch (error) {
        console.log(error);
    }
    const mandanten = wam.Mandanten;
    const saisons = wam.Saisons;
    const competitons = wam.CompetitionTypes;

    const mandanten_map = new Map();
    const saisons_map = new Map();
    const competitonsTypes_map = new Map();
    const combinations = [];


    Object.keys(mandanten).forEach(key => {
        mandanten_map.set(formatKey(key), mandanten[key]);
        const formatted_mandant = formatKey(key);

        Object.keys(saisons[formatted_mandant]).forEach(key => {
            saisons_map.set(formatKey(key), saisons[formatted_mandant][key]);
            const formatted_saison = formatKey(key);

            Object.keys(competitons[formatted_mandant][formatted_saison]).forEach(key => {
                competitonsTypes_map.set(formatKey(key), competitons[formatted_mandant][formatted_saison][key]);
                combinations.push([formatted_mandant, formatted_saison, formatKey(key)]);
            });
        });
    });

    return ({
        mandanten: Array.from(mandanten_map),
        saisons: Array.from(saisons_map),
        competition_types: Array.from(competitonsTypes_map),
        competitions: combinations
    });
}


async function connectDb() {
    try {
        const client = new Client({
            user: process.env.PGUSER,
            host: process.env.PGHOST,
            database: process.env.PGDATABASE,
            password: process.env.PGPASSWORD,
            port: process.env.PGPORT
        })

        await client.connect()
        console.log("Connected successfully")
        return client
    } catch (error) {
        console.log(error)
    }
}


async function saveCompetitionsInDatabase() {
    const client = await connectDb();

    const base_data = await loadKinds();
    console.log('Base data loaded')


    for (const base of Object.keys(base_data)) {
        const dataList = base_data[base];
        if (base !== 'competitions') {
            client.query(format('INSERT INTO fussball.%s (id, name) VALUES %L ON CONFLICT DO NOTHING', base, dataList), [], (err, result) => {
                if (err) {
                    console.log(err);
                }
            });
            continue;
        }
        client.query(
            format('INSERT INTO fussball.%s (mandanten_id, saison_id, competition_id) VALUES %L ON CONFLICT DO NOTHING', base, dataList), [],
            (err, result) => {
                if (err) {
                    console.log(err);
                }
            });
    }
}

async function getKindFiles(){
    const client = await connectDb();
    const res = await client.query('SELECT * FROM fussball.competitions WHERE saison_id = \'2223\'');
    const competitions = res.rows;

    for(const competition of competitions){
        await axios.get(format('https://www.fussball.de/wam_kinds_%s_%s_%s.json', competition.mandanten_id, competition.saison_id, competition.competition_id))
            .then(response => {
                const fileName = format('wam_kinds_%s_%s_%s.json', competition.mandanten_id, competition.saison_id, competition.competition_id)
                fs.writeFile(`data/kinds/${fileName}`, JSON.stringify(response.data, null, "\t"), (err) => {
                    if (err) throw err;
                    console.log(format('wam_kinds_%s_%s_%s.json saved', competition.mandanten_id, competition.saison_id, competition.competition_id));
                });
            })
    }
}

function getRegions(filePath) {
    let wamKinds = undefined;
    try {
        wamKinds = JSON.parse(fs.readFileSync(filePath, 'utf8'));
    } catch (error) {
        console.log(error);
    }

    const mannschaftsart = wamKinds.Mannschaftsart;
    const spielklasse = wamKinds.Spielklasse;
    const gebiet = wamKinds.Gebiet;

    const mannschaftsart_map = new Map();
    const spielklasse_map = new Map();
    const gebiet_map = new Map();
    const ligen = [];



    Object.keys(mannschaftsart).forEach(key => {
        mannschaftsart_map.set(formatKey(key), mannschaftsart[key]);
        const formatted_mannschaftsart = formatKey(key);

        Object.keys(spielklasse[formatted_mannschaftsart]).forEach(key => {
            spielklasse_map.set(formatKey(key), spielklasse[formatted_mannschaftsart][key]);
            const formatted_spielklasse = formatKey(key);

            Object.keys(gebiet[formatted_mannschaftsart][formatted_spielklasse]).forEach(key => {
                gebiet_map.set(formatKey(key), gebiet[formatted_mannschaftsart][formatted_spielklasse][key]);
                ligen.push([formatted_mannschaftsart, formatted_spielklasse, formatKey(key)]);
            });
        });
    });

    return ({
        mannschaften: Array.from(mannschaftsart_map),
        spielklassen: Array.from(spielklasse_map),
        gebiete: Array.from(gebiet_map),
        ligen: ligen
    });
}


async function saveRegionsInDatabase(obj, client) {

    for (const base of Object.keys(obj)) {
        const dataList = obj[base];
        if (base !== 'ligen') {
            client.query(format('INSERT INTO fussball.%s (id, name) VALUES %L ON CONFLICT DO NOTHING', base, dataList), [], (err, result) => {
                if (err) {
                    console.log(err);
                }
            });
            continue;
        }
        client.query(
            format('INSERT INTO fussball.%s (mannschafts_id, spielkassen_id, gebiet_id) VALUES %L ON CONFLICT DO NOTHING', base, dataList), [],
            (err, result) => {
                if (err) {
                    console.log(err);
                }
            });
    }
}

async function saveRegionsFromFiles(filePath, client){
    const files = fs.readdirSync(filePath);
    files.forEach(async file => {
        const obj = await getRegions(filePath + '/' + file);
        await saveRegionsInDatabase(obj, client);
        console.log(format('%s saved', file));
    })


}



function formatKey(key) {
    return key.replace('_', '');
}