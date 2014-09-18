

deep_find ?= $(wildcard $(patsubst %,$(1)%$(2),* */* */*/* */*/*/*))

SITE       = ftp://la02.neti.ee/
SOURCE     = public/
TARGET     = /
ALL_FILES  = $(call deep_find,$(SOURCE),*.*)


.PHONY: ftp-upload ftp-list

ftp-upload: .uploaded

ftp-list:
	# curl -n $(SITE) -Q 'RMD path/to/folder'
	# curl -n $(SITE) -Q 'DELE path/to/file'
	curl -n $(SITE)$(TARGET)

.uploaded: $(ALL_FILES)
	curl -n --ftp-create-dirs \
		$(foreach f,$?,-T $(f) $(SITE)$(patsubst $(SOURCE)%,$(TARGET)%,$(f)))
	@touch $@


# $ curlftpfs ftp://la02.neti.ee/ live
# $ fusermount -u live
# 	lftp ftp://$(FTP_USER)@$(FTP_HOST) -e "mirror -R $(OUTPUTDIR) $(FTP_TARGET_DIR) ; quit"
#
#
