<?php

abstract class MwsOrderEvent extends MwsBasicEnum
{

	public const OrderCloseMailSend = 'order_close_mail_send';

	public const OrderSetPaid = 'order_set_paid';
	public const OrderSetUnpaid = 'order_set_unpaid';

	public const PaymentFailed = 'payment_failed';

	public const OrderStatusChangeToOrdered = 'order_status_change_to_' . MwsOrderStatus::Ordered;
	public const OrderStatusChangeToProcessing = 'order_status_change_to_' . MwsOrderStatus::Processing;
	public const OrderStatusChangeToClosed = 'order_status_change_to_' . MwsOrderStatus::Closed;
	public const OrderStatusChangeToCancelled = 'order_status_change_to_' . MwsOrderStatus::Cancelled;

	public const InvoiceCreated = 'invoice_created';
	public const InvoiceMailSend = 'invoice_mail_send';

	public const CustomerEdited = 'customer_edited';

	public const PacketaCreated = 'packeta_created';
	public const PacketaFailed = 'packeta_failed';

	public const OrderArchived = 'order_archived';
	public const OrderDeArchived = 'order_deArchived';

	public const MPohodaInvoiceIssued = 'mpohoda_invoice_issued';
	public const MPohodaInvoiceIssueFailed = 'mpohoda_invoice_issue_failed';

}
