import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { IssueOfSharesComponent } from './issue-of-shares.component';

describe('IssueOfSharesComponent', () => {
  let component: IssueOfSharesComponent;
  let fixture: ComponentFixture<IssueOfSharesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ IssueOfSharesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(IssueOfSharesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
