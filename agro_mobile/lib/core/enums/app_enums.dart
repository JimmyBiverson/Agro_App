enum OrderStatus {
  pending('Pending'),
  approved('Approved'),
  declined('Declined'),
  adjusted('Adjusted'),
  delivered('Delivered'),
  cancelled('Cancelled');

  final String displayName;
  const OrderStatus(this.displayName);
}

enum PaymentStatus {
  pending('Pending'),
  verified('Verified'),
  accepted('Accepted'),
  rejected('Rejected');

  final String displayName;
  const PaymentStatus(this.displayName);
}

enum InventoryAlertLevel {
  normal('Normal'),
  low('Low Stock'),
  critical('Critical'),
  outOfStock('Out of Stock');

  final String displayName;
  const InventoryAlertLevel(this.displayName);
}

enum NetworkConnectionState {
  disconnected('Disconnected'),
  connecting('Connecting'),
  connected('Connected'),
  error('Error');

  final String displayName;
  const NetworkConnectionState(this.displayName);
}
