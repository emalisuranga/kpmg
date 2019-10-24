export interface ISocietyData {

  id: number;
  name_of_society: number;
  place_of_office: number;
  whole_of_the_objects: number;
  funds: number;
  terms_of_admission: number;
  condition_under_which_any: number;
  fines_and_foreitures: number;
  mode_of_holding_meetings: number;
  manner_of_rules: number;
  investment_of_funds: number;
  keeping_accounts: number;
  audit_of_the_accounts: number;
  annual_returns: number;
  number_of_members: number;
  inspection_of_the_books: number;
  disputes_manner: number;
  case_of_society: number;
  email: string;
  appointment_and_removal_committee: number;
  applicability: string;

}

export interface IPresident {
  id: number;
  is_affidavit: number;
  type: number;
  fullname: string;
  designation_soc: string;
  divisional_secretariat: string;
  province: any;
  district: any;
  city: any;
  gnDivision: any;
  localAddress1: string;
  localAddress2: string;
  postcode: string;
  nic: string;
  designation_type: number;
  contact_number: number;
  showEditPaneForPresident: number;
  email: string;

}

export interface IPresidents {
  presidents: Array<IPresident>;
}

export interface ISecretary {
  id: number;
  is_affidavit: number;
  type: number;
  fullname: string;
  province: any;
  district: any;
  city: any;
  gnDivision: any;
  designation_soc: string;
  divisional_secretariat: string;
  localAddress1: string;
  localAddress2: string;
  postcode: string;
  nic: string;
  designation_type: number;
  contact_number: number;
  showEditPaneForSecretary: number;
  email: string;

}
export interface ITreasurer {
  id: number;
  is_affidavit: number;
  type: number;
  fullname: string;
  province: any;
  district: any;
  city: any;
  gnDivision: any;
  designation_soc: string;
  divisional_secretariat: string;
  localAddress1: string;
  localAddress2: string;
  postcode: string;
  nic: string;
  designation_type: number;
  contact_number: number;
  showEditPaneForTreasurer: number;
  email: string;

}

export interface IAddit {
  id: number;
  is_affidavit: number;
  type: number;
  fullname: string;
  province: any;
  district: any;
  city: any;
  gnDivision: any;
  designation_soc: string;
  divisional_secretariat: string;
  localAddress1: string;
  localAddress2: string;
  postcode: string;
  nic: string;
  designation_type: number;
  contact_number: number;
  showEditPaneForAddit: boolean;
  email?: string;

}

export interface IMemb {

  id: number;
  is_affidavit: number;
  type: number;
  fullname: string;
  province: any;
  district: any;
  city: any;
  gnDivision: any;
  designation_soc: string;
  divisional_secretariat: string;
  localAddress1: string;
  localAddress2: string;
  postcode: string;
  nic: string;
  designation_type: number;
  contact_number: number;
  showEditPaneForMemb: boolean;
  passport: string;
  country: string;
  email?: string;
}
