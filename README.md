# HW_assignmentCOS216

Database Update Strategy
For this project, we chose to update the database every time there is an update so whenever a new order is created, an order is changed, or a drone’s position/status changes, the server immediately calls the API to update the database.

Motivation
Real-time accuracy: This approach ensures that the database always reflects the current state of the system. If a user refreshes their page or a new client connects, they will always see the latest information.
Simplicity: There is no need to keep a local history or cache of orders or drone statuses on the server. This reduces the risk of data inconsistencies and makes the server logic easier to maintain.
Immediate feedback: Customers and couriers receive instant updates about their orders and deliveries, which is important for a real-time delivery system.
No risk of data loss: If the server crashes or restarts, all updates have already been written to the database, so nothing is lost.

Updating the database on every change is the better option for this real-time, multi-user system because it ensures data consistency, reliability, and a better user experience.