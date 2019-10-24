export interface IAddressData {

    id: number;
    province: any;
    district: any;
    city: any;
    gnDivision: any;
    localAddress1: string;
    localAddress2: string;
    postcode: string;


}

export interface IOldAddressData {

    id: number;
    province: any;
    district: any;
    city: any;
    gnDivision: any;
    localAddress1: string;
    localAddress2: string;
    postcode: string;

}

export interface IAcAddressData {

    id: number;
    type: number;
    province: any;
    district: any;
    city: any;
    gnDivision: any;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    date: any;
    showEditPaneForPresident: any;
    country?: string;


}

export interface IAcOldAddressData {

    id: number;
    type: number;
    province: any;
    district: any;
    city: any;
    gnDivision: any;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    date: any;
    showEditPaneForPresident: any;
    country?: string;


}

export interface IAcAddressResubmitData {

    id: number;
    type: number;
    province: any;
    district: any;
    city: any;
    gnDivision: any;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    date: any;
    showEditPaneForPresident: any;
    bool: any;
    country?: string;


}

export interface IRcAddressData {

    id: number;
    province: any;
    district: any;
    city: any;
    gnDivision: any;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    date: any;
    showEditPaneForPresident: any;
    discription: string;


}

export interface IRcOldAddressData {

    id: number;
    province: any;
    district: any;
    city: any;
    gnDivision: any;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    date: any;
    showEditPaneForPresident: any;
    discription: string;
    newid?: any;
    isdeleted?: any;
    type?: any;


}

export interface IMrAddressData {

    id: number;
    province: any;
    district: any;
    city: any;
    gnDivision: any;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    date: any;
    showEditPaneForPresident: any;
    discription: string;


}

export interface IMrOldAddressData {

    id: number;
    province: any;
    district: any;
    city: any;
    gnDivision: any;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    date: any;
    showEditPaneForPresident: any;
    discription: string;
    newid?: any;
    isdeleted?: any;
    type?: any;


}

export interface ISrAddressData {

    id: number;
    province: any;
    district: any;
    city: any;
    gnDivision: any;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    date: any;
    showEditPaneForPresident: any;
    discription: string;


}

export interface ISrOldAddressData {

    id: number;
    province: any;
    district: any;
    city: any;
    gnDivision: any;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    date: any;
    showEditPaneForPresident: any;
    discription: string;
    newid?: any;
    isdeleted?: any;
    type?: any;


}
