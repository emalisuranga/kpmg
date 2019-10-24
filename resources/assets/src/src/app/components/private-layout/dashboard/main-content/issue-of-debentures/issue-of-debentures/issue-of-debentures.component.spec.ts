import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { IssueOfDebenturesComponent } from './issue-of-debentures.component';

describe('IssueOfDebenturesComponent', () => {
  let component: IssueOfDebenturesComponent;
  let fixture: ComponentFixture<IssueOfDebenturesComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ IssueOfDebenturesComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(IssueOfDebenturesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
