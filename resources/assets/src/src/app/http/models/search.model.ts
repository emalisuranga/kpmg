export interface ISearch {
  criteria?: number;
  searchtext: string;
  companyType?: number;
  token: string;
}

export class IHas {
  availableData: IHasMeny;
  notHasData: IHasMeny;
}

export class IHasMeny {
  available: boolean;
  data: Array<any>;
  meta: IPaginate;
}

export class ISeResult {
  id: object;
  name: object;
  postfix: string;
  registration_no: string;
}

export class IPaginate {
  last_page: number;
  per_page: number;
  total: number;
}

export class ICompanyType {
  id: number;
  value: string;
}

export class IGetOtherDocs {
  company_id: string;
}

export class IGetOtherDocsForName {
  company_id: string;
  type: number;
}

export interface INamereceive {
  email: string;
  object: string;
  englishName: string;
  sinhalaName: string;
  tamilname: string;
  postfix: string;
  abreviations: string;
  nametype: string;
}

export class IUpdateCourtDetails {
  new_company_id: string;
  court_status: string;
  court_name: string;
  court_case_no: string;
  court_date: string;
  court_penalty: string;
  court_period: string;
  court_discharged?: string;
}


