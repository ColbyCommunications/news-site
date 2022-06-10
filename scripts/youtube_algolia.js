const superagent = require('superagent');
const algoliasearch = require('algoliasearch');

function getArgs() {
    const args = {};
    process.argv.slice(2, process.argv.length).forEach((arg) => {
        // long arg
        if (arg.slice(0, 2) === '--') {
            const longArg = arg.split('=');
            const longArgFlag = longArg[0].slice(2, longArg[0].length);
            const longArgValue = longArg.length > 1 ? longArg[1] : true;
            args[longArgFlag] = longArgValue;
        }
        // flags
        else if (arg[0] === '-') {
            const flags = arg.slice(1, arg.length).split('');
            flags.forEach((flag) => {
                args[flag] = true;
            });
        }
    });
    return args;
}

// get args
const args = getArgs();

// initialize algolia client
const client = algoliasearch(args.algoliaAppId, args.algoliaApiKey);
const index = client.initIndex(args.algoliaIndexName);

async function main() {
    // clear algolia index
    await index.clearObjects().wait();

    // begin youtube processing
    let algoliaRecords = [];
    superagent
        .get(
            `https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,contentDetails&maxResults=1000&playlistId=UUhGBTvH9tUJbjxiaAAHGPqg&key=${args.apiKey}`
        )
        .then(async (res) => {
            res.body.items.forEach((item) => {
                // save local vars
                let videoId = item.contentDetails.videoId;
                let publishedAt = item.contentDetails.videoPublishedAt;
                let title = item.snippet.title;
                let description = item.snippet.description;
                let thumbStandard = item.snippet.thumbnails.standard;

                const pattern = '►';

                let algoliaVideoRecord = {
                    objectID: videoId,
                    videoId,
                    publishedDate: publishedAt,
                    title,
                    description: description.slice(0, description.indexOf(pattern)),
                    thumbnail: thumbStandard,
                };

                algoliaRecords.push(algoliaVideoRecord);
            });

            await index.saveObjects(algoliaRecords).wait();
        })
        .catch((err) => {
            console.log(err);
        });
}

main().catch(console.error);
