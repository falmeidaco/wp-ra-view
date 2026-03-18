const validator     = require('gltf-validator');
const JSONFormatter = require('json-formatter-js');

window.gltfValidate = function (url) {
  return fetch(url)
    .then(function (res) { return res.arrayBuffer(); })
    .then(function (asset) { return validator.validateBytes(new Uint8Array(asset)); });
};

window.JSONFormatter = JSONFormatter;
