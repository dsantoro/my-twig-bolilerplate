all: _site _admin

_site:
	make -C ./site/static
_admin:
	make -C ./admin/static