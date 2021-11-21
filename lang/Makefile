INSTALLDIR = .
LANGUAGES = `find ./ -name "auto_*.po" -print | sed 's|^\./auto_\(.*\).po|\1|'`
DOMAINS = auto
PHP_SOURCES = $(shell find ../ -maxdepth 1 -name \*.php) \
              $(shell find ../lib/GaletteAuto/ -name \*.php) \
              $(shell find ../templates -name \*.tpl)


all : extract mo

po :
	@echo "Generating PO files:"
	@for l in ${LANGUAGES}; do \
	  for d in ${DOMAINS}; do \
	    if [ -f $${d}_$${l}.po ]; then \
	      echo -n "  Updating $${d}_$${l}.po"; \
	      msgmerge -U $${d}_$${l}.po $$d.pot >/dev/null ; \
	    else \
	      echo "  Creating of $${d}_$${l}.po"; \
	      msginit -o $${d}_$${l}.po -i $$d.pot >/dev/null ; \
	    fi; \
	  done \
	done

mo :
	@echo "Generating MO files:"
	@for l in ${LANGUAGES}; do \
	  for d in ${DOMAINS}; do \
	    mkdir -p ${INSTALLDIR}/$${l}/LC_MESSAGES; \
	    echo "    formatting ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo."; \
	    msgfmt $${d}_$${l}.po -o ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo.new; \
	    if diff -qI 'PO-Revision-Date:.*' ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo.new ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo > /dev/null; then \
	      echo "    $${d}.mo NOT updated."; \
	      rm ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo.new; \
	    else \
	      echo "    $${d}.mo UPDATED."; \
	      mv ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo.new ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo; \
	    fi; \
	  done \
	done

extract : ${PHP_SOURCES} Makefile
	xgettext ${PHP_SOURCES} --keyword=_T:1,2t --keyword=__:1,2t --keyword=_Tn:1,2,4t --keyword=_Tx:1c,2,3t --keyword=_Tnx:1c,2,3,5t -L PHP --from-code=UTF-8 --add-comments=TRANS --force-po -o auto_php.pot;
	../../../vendor/bin/smarty-xgettext --domain auto -o auto_tpl.pot ../templates/default/; \
	msgcat -o auto.pot --use-first auto_php.pot auto_tpl.pot; \
	rm auto_php.pot auto_tpl.pot; \
	echo "Generating en_US:"
	@for d in ${DOMAINS}; do \
	  LANG=C msgen $$d.pot -o $${d}_en_US.po; \
	done
