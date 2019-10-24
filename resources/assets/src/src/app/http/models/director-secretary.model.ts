export interface IDirector {
    type: string;
    title: string;
    firstname: string;
    lastname: string;
    province: string;
    district: string;
    city: string;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    passport_issued_country?: string;

    forProvince?: string;
    forCity?: string;
    forAddress1?: string;
    forAddress2?: string;
    forPostcode?: string;

    nic: string;
    passport: string;
    country: string;
    share: number;
    date: string;
    occupation: string;

    phone: string;
    mobile: string;
    email: string;

    id: number;

    isSec?: boolean; // is secretory
    secRegDate?: string;
    isSecEdit?: boolean;
    isShareholder?: boolean;
    isShareholderEdit?: boolean;

    shareType?: string;
    noOfSingleShares?: number;
    coreGroupSelected?: number;
    coreShareGroupName?: string;
    coreShareValue?: number;

    shareTypeEdit?: string;
    noOfSingleSharesEdit?: number;
    coreGroupSelectedEdit?: number;
    coreShareGroupNameEdit?: string;
    coreShareValueEdit?: number;

    showEditPaneForDirector: number;
    directors_as_sec?: number;
    directors_as_sh?: number;

    screen1Provinces?: Array<IProvince>;
    screen1Districts?: Array<IDistrict>;
    screen1Cities?: Array<ICity>;
    can_director_as_sec?: boolean;

}


export interface IDirectors {
    directors: Array<IDirector>;
}

export interface ISecretory {
    id: number;
    type: string;
    title: string;
    firstname: string;
    lastname: string;

    province: string;
    district: string;
    city: string;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    passport_issued_country?: string;

    nic: string;
    passport: string;
    country: string;
    share: number;
    date: string;
    occupation: string;

    isReg: boolean;
    regDate: string;
    phone: string;
    mobile: string;
    email: string;

    isShareholder?: boolean;
    isShareholderEdit?: boolean;

    sec_as_sh?: number;
    sec_sh_comes_from_director?: boolean;

    shareType?: string; // single or core
    noOfSingleShares?: number;
    coreGroupSelected?: number;
    coreShareGroupName?: string;
    coreShareValue?: number;

    shareTypeEdit?: string; // single or core
    noOfSingleSharesEdit?: number;
    coreGroupSelectedEdit?: number;
    coreShareGroupNameEdit?: string;
    coreShareValueEdit?: number;

    secType?: string; // natural or firm
    secCompanyFirmId?: string;
    pvNumber?: string;
    firm_name?: string;
    firm_province?: string;
    firm_district?: string;
    firm_city?: string;
    firm_localAddress1?: string;
    firm_localAddress2?: string;
    firm_postcode?: string;
    firm_email?: string;
    firm_mobile?: string;
    firm_phone?: string;
    savedSec?: string;

    firm_info?: IfirmInfo;

    showEditPaneForSec: number;
    forAddress1?: string;
    forAddress2?: string;
    forAddress3?: string;

    forProvince?: string;
    forCity?: string;
    forPostcode?: string;

    screen1Provinces?: Array<IProvince>;
    screen1Districts?: Array<IDistrict>;
    screen1Cities?: Array<ICity>;

    benifOwnerType?: string;
    secBenifList?: IShareHolderBenifList;
    validateSecShBenifInEdit?: boolean;
}

export interface IfirmInfo {
    registration_no?: string;
    name?: string;
    address?: IformInfoAddress;
}

export interface IformInfoAddress {
    province?: string;
    district?: string;
    city?: string;
    address1?: string;
    address2?: string;
    postcode?: string;
}

export interface ISecretories {
    secs: Array<ISecretory>;
}

export interface IShareHolder {
    type: string;
    title: string;
    firstname: string;
    lastname: string;

    province: string;
    district: string;
    city: string;
    localAddress1: string;
    localAddress2: string;
    postcode: string;

    forProvince?: string;
    forCity?: string;
    forAddress1?: string;
    forAddress2?: string;
    forPostcode?: string;


    nic: string;
    passport: string;
    country: string;
    passport_issued_country?: string;
    share: number;
    date: string;
    occupation: string;
    phone: string;
    mobile: string;
    email: string;
    id: number;

    shareholderType?: string;
    benifOwnerType?: string;

    shareholderFirmCompanyisForiegn?: boolean;
    pvNumber?: string;
    firm_name?: string;
    firm_province?: string;
    firm_district?: string;
    firm_city?: string;
    firm_localAddress1?: string;
    firm_localAddress2?: string;
    firm_postcode?: string;
    firm_email?: string;
    firm_mobile?: string;
    firm_phone?: string;
    shareType: string;
    noOfShares: number;
    coreGroupSelected?: number;
    coreShareGroupName?: string;
    noOfSharesGroup?: number;
    showEditPaneForSh: number;
    shareRow?: IShareRow;
    benifiList?: IShareHolderBenifList;
    validateAddBenif?: boolean;

    screen1Provinces?: Array<IProvince>;
    screen1Districts?: Array<IDistrict>;
    screen1Cities?: Array<ICity>;

}
export interface IShareHolders {
    shs: Array<IShareHolder>;
}

export interface IShareHolderBenif {
    title: string;
    firstname: string;
    lastname: string;
    province: string;
    district: string;
    city: string;
    localAddress1: string;
    localAddress2: string;
    postcode: string;
    nic: string;
    passport: string;
    country: string;
    date: string;
    occupation: string;
    phone: string;
    mobile: string;
    email: string;
    id?: number;
    type: string;

    screen1Provinces?: Array<IProvince>;
    screen1Districts?: Array<IDistrict>;
    screen1Cities?: Array<ICity>;
}

export interface IShareHolderBenifList {
    ben: Array<IShareHolderBenif>;
}


export interface IShareRow {
    type?: string;
    no_of_shares?: number;
    name?: string;
    sharegroupId?: number;
}



export interface INICchecker {
    companyId: string;
    nic: string;
    memberType: number;
}

export interface IStakeholderDelete {
    userId: number;
    companyId: string;
}

export interface ISecForDirDelete {
    userId: number;
    companyId: string;
}
export interface IShForDirDelete {
    userId: number;
    companyId: string;
}

export interface IShForSecDelete {
    userId?: number;
    firmId?: number;
    companyId: string;
}

export interface IFileRemove {
    companyId: number;
    docTypeId: number;
    userId?: number;
    multipleId?: number;
    isFirm?: string;
}
export interface IProvince {
    id: number;
    name: string;
}
export interface IDistrict {
    id: number;
    provinceName: string;
    name: string;
}
export interface ICity {
    id: number;
    districtName: string;
    name: string;
}

export interface IGnDivision {
    id: number;
    cityName: string;
    name: string;
}

export interface IObjective {
    id: number;
    code: string;
    name: string;
    parent_id: number;
}

export interface IObjectiveRow {
    objective1: string;
    objective2: string;
    objective3: string;
    objective4: string;
    objective5: string;
    level1Objectives?: Array<IObjective>;
    level2Objectives?: Array<IObjective>;
    level3Objectives?: Array<IObjective>;
    level4Objectives?: Array<IObjective>;
    level5Objectives?: Array<IObjective>;

}

export interface IObjectiveCollection {
    collection: Array<IObjectiveRow>;
}


export interface IIncorporationMembers {
    companyId: string;
    requestId: string;
    directors: IDirectors;
    secretories: ISecretories;
    shareholders: IShareHolders;
    action?: string;
}

export interface IMemberRemove {
    userId: number;
    companyId: string;
    type: string;
}

export interface IOldMemberRemove {
    userId: number;
    companyId: string;
    type: string;
    reason: string;
    date: string;
}
export interface IMemberFile {
    companyId: string;
    type: string;
}

export interface IResubmitMemberChange {
    companyId: string;
}

