import {nodeResolve} from '@rollup/plugin-node-resolve';
import terser from '@rollup/plugin-terser';
export default {input: 'editor.js', output: {file: 'editor.bundle.js', format: 'iife', name: 'SlowcloudCodeMirror', exports: 'named'}, plugins: [nodeResolve(), terser()]};
