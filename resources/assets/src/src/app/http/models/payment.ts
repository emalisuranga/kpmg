export class IBuy {
  module_type: string;
  module_id: string;
  description: string;
  extraPay?: string;
  item: Array<Item>;
  penalty?: string;
  delevery_option?: string;
}

export class Item {
  fee_type: string;
  description: string;
  quantity: number;
}
