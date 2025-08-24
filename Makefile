up:
	@docker compose up --build -d
	@docker compose logs

enter:
	@docker exec -it cronjob-dev sh

down:
	@docker compose down