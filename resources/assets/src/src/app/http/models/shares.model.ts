export interface ISharesData {

    id: number;
    showEditPaneForMemb: boolean;
    typeofshare: any;
    dateofissue: any;
    issuedshares: number;
    noofsharesascash: number;
    consideration: number;
    noofsharesasnoncash: number;
    considerationotherthancash: number;
    considerationotherthancashtext: string;
    cashapplicability: number;
    noncashapplicability: number;
}

export interface IShareType {
    id: number;
    value: string;
}

export class ICountry {
    id: string;
    name: string;
  }

export class ICsvSupport {
    id: string;
    title: string;
    countryname: string;
    province: any;
    district: any;
    city: any;
  }


