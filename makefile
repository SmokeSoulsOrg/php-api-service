up:
	./vendor/bin/sail up -d

down:
	./vendor/bin/sail down -v && bash scripts/post-down.sh
