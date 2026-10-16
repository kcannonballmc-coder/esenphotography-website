/**
 * Local preview of the built site: node site/serve.mjs  ->  http://localhost:4173
 * Run `node site/build.mjs` first.
 */
import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { extname, join, normalize } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = fileURLToPath( new URL( '../_site/', import.meta.url ) );
const PORT = Number( process.env.PORT || 4173 );
const TYPES = { '.html': 'text/html; charset=utf-8', '.css': 'text/css', '.js': 'text/javascript', '.json': 'application/json', '.jpg': 'image/jpeg', '.png': 'image/png', '.svg': 'image/svg+xml', '.xml': 'application/xml', '.txt': 'text/plain' };

createServer( async ( req, res ) => {
	const path = decodeURIComponent( new URL( req.url, 'http://localhost' ).pathname );
	let file = normalize( join( ROOT, path ) );
	if ( ! file.startsWith( normalize( ROOT ) ) ) {
		res.writeHead( 403 ).end();
		return;
	}
	try {
		if ( ( await stat( file ) ).isDirectory() ) {
			file = join( file, 'index.html' );
		}
		const body = await readFile( file );
		res.writeHead( 200, { 'Content-Type': TYPES[ extname( file ) ] || 'application/octet-stream' } ).end( body );
	} catch {
		res.writeHead( 404, { 'Content-Type': TYPES[ '.html' ] } ).end( await readFile( join( ROOT, '404.html' ) ).catch( () => 'Not found' ) );
	}
} ).listen( PORT, () => console.log( `Preview: http://localhost:${ PORT }` ) );
