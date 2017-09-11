JS_APP_FILES := $(wildcard js/app/**) $(wildcard js/app/**/*) $(wildcard js/app/**/**/*) js/app.js
JS_LIB_FILES := $(wildcard js/lib/**) $(wildcard js/lib/**/*)
LESS_FILES := $(wildcard css/*.less) $(wildcard css/**/*.less)

all: js/app.min.js  css/style.min.css

css/style.css: $(LESS_FILES)
	lessc --plugin=less-plugin-clean-css="--advanced --s0" --source-map  css/style.less css/style.css

css/style.min.css: css/style.css
	node tools/r.js -o cssIn=css/style.css out=css/style.min.css

js/app.min.js: $(JS_APP_FILES) $(JS_LIB_FILES)
	node tools/r.js -o tools/build.js
clean:
	rm css/style.css css/style.min.css js/app.min.js