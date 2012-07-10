all: gui core

gui: gui/default/js/gui.js

gui/default/js/gui.js: gui/default/js/gui_functions.js gui/default/js/gui_search.js gui/default/js/gui_help.js gui/default/localjs/import.js gui/default/localjs/show.js gui/default/localjs/verblist.js gui/default/localjs/verbshow.js gui/default/localjs/queryl.js gui/default/localjs/settings.js gui/default/localjs/content.js
	java -jar compiler/compiler.jar --js=gui/default/js/gui_functions.js  --js=gui/default/js/gui_search.js --js=gui/default/js/gui_help.js --js=gui/default/localjs/import.js --js=gui/default/localjs/show.js --js=gui/default/localjs/verblist.js --js=gui/default/localjs/verbshow.js --js=gui/default/localjs/queryl.js --js=gui/default/localjs/settings.js --js=gui/default/localjs/content.js --js_output_file=gui/default/js/gui.js

core: core/javascript/core.js

core/javascript/core.js: core/javascript/library/prototype.js core/javascript/functions.js core/javascript/query.js
	java -jar compiler/compiler.jar --js=core/javascript/library/prototype.js --js=core/javascript/functions.js --js=core/javascript/query.js --js_output_file=core/javascript/core.js

