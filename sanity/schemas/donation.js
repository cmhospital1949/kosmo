export default {
  name: 'donation',
  title: 'Donation',
  type: 'document',
  fields: [
    {
      name: 'amount',
      title: 'Amount (KRW)',
      type: 'number',
      validation: Rule => Rule.required().positive()
    },
    {
      name: 'paymentGateway',
      title: 'Payment Gateway',
      type: 'string',
      options: {
        list: [
          {title: 'KakaoPay', value: 'kakaopay'},
          {title: 'NaverPay', value: 'naverpay'},
          {title: 'Bank Transfer', value: 'bank'}
        ]
      }
    },
    {
      name: 'transactionId',
      title: 'Transaction ID',
      type: 'string'
    },
    {
      name: 'status',
      title: 'Status',
      type: 'string',
      options: {
        list: [
          {title: 'Pending', value: 'pending'},
          {title: 'Paid', value: 'paid'},
          {title: 'Error', value: 'error'}
        ]
      },
      initialValue: 'pending'
    },
    {
      name: 'donorName',
      title: 'Donor Name',
      type: 'string'
    },
    {
      name: 'donorEmail',
      title: 'Donor Email',
      type: 'string'
    },
    {
      name: 'isAnonymous',
      title: 'Anonymous Donation',
      type: 'boolean',
      initialValue: false
    },
    {
      name: 'issueReceipt',
      title: 'Issue Receipt',
      type: 'boolean',
      initialValue: false
    },
    {
      name: 'createdAt',
      title: 'Created At',
      type: 'datetime',
      initialValue: (new Date()).toISOString(),
      readOnly: true
    }
  ],
  preview: {
    select: {
      title: 'donorName',
      subtitle: 'amount',
      status: 'status'
    },
    prepare(selection) {
      const { title, subtitle, status } = selection;
      return {
        title: title || 'Anonymous',
        subtitle: `${subtitle?.toLocaleString()} KRW (${status})`,
      }
    }
  }
}
