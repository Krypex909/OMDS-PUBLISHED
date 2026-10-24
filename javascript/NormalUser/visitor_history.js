const visitors = [
  {
    date: "09/20/2026",
    time: "09:15 AM",
    visitorName: "Benjo Dela Cruz",
    visitorID: "8261645901",
  },
  {
    date: "09/18/2026",
    time: "02:30 PM",
    visitorName: "Marie Anderson",
    visitorID: "0926451836",
  },
  {
    date: "09/15/2026",
    time: "10:45 AM",
    visitorName: "John Reyes",
    visitorID: "4589867198",
  },
  {
    date: "09/12/2026",
    time: "03:20 PM",
    visitorName: "Robert Garcia",
    visitorID: "8932486432",
  },
  {
    date: "09/10/2026",
    time: "11:00 AM",
    visitorName: "Bonji Paras",
    visitorID: "0018345492",
  },
  {
    date: "09/28/2026",
    time: "12:15 PM",
    visitorName: "Marc Laurence",
    visitorID: "2024136340",
  },
  {
    date: "09/14/2026",
    time: "03:30 PM",
    visitorName: "Captain Price",
    visitorID: "8904336513",
  },
  {
    date: "09/15/2026",
    time: "08:45 AM",
    visitorName: "Lucia Barroso",
    visitorID: "5468645902",
  },
  {
    date: "09/12/2026",
    time: "03:55 PM",
    visitorName: "Roberto Del Rosario",
    visitorID: "6842153864",
  },
  {
    date: "09/09/2026",
    time: "11:00 AM",
    visitorName: "Randy Jackson",
    visitorID: "1185934256",
  },
  {
    date: "09/08/2026",
    time: "08:30 AM",
    visitorName: "Daniel Santos",
    visitorID: "5738291046",
  },
  {
    date: "09/07/2026",
    time: "01:45 PM",
    visitorName: "Angela Mendoza",
    visitorID: "6842105739",
  },
  {
    date: "09/06/2026",
    time: "10:20 AM",
    visitorName: "Patrick Wilson",
    visitorID: "3917462058",
  },
  {
    date: "09/05/2026",
    time: "02:10 PM",
    visitorName: "Sofia Ramirez",
    visitorID: "8251036497",
  },
  {
    date: "09/04/2026",
    time: "09:50 AM",
    visitorName: "Michael Torres",
    visitorID: "4628091753",
  },
  {
    date: "09/03/2026",
    time: "03:15 PM",
    visitorName: "Isabella Flores",
    visitorID: "7193548260",
  },
  {
    date: "09/02/2026",
    time: "11:35 AM",
    visitorName: "Christopher Lee",
    visitorID: "0538172946",
  },
  {
    date: "09/01/2026",
    time: "04:05 PM",
    visitorName: "Gabriella Cruz",
    visitorID: "9462817350",
  },
  {
    date: "08/30/2026",
    time: "08:55 AM",
    visitorName: "Nathan Garcia",
    visitorID: "2816409573",
  },
  {
    date: "08/29/2026",
    time: "01:25 PM",
    visitorName: "Camille Reyes",
    visitorID: "6371954820",
  },
  {
    date: "08/28/2026",
    time: "10:40 AM",
    visitorName: "Adrian Castillo",
    visitorID: "1048293765",
  },
  {
    date: "08/27/2026",
    time: "02:50 PM",
    visitorName: "Nicole Fernandez",
    visitorID: "8526371940",
  },
  {
    date: "08/26/2026",
    time: "09:05 AM",
    visitorName: "Joshua Martinez",
    visitorID: "3905718264",
  },
  {
    date: "08/25/2026",
    time: "03:40 PM",
    visitorName: "Patricia Aquino",
    visitorID: "7612049853",
  },
  {
    date: "08/24/2026",
    time: "11:15 AM",
    visitorName: "Kenneth Ramos",
    visitorID: "5284167309",
  },
  {
    date: "08/23/2026",
    time: "01:55 PM",
    visitorName: "Andrea Villanueva",
    visitorID: "1937504826",
  },
  {
    date: "08/22/2026",
    time: "08:20 AM",
    visitorName: "Mark Thompson",
    visitorID: "6402819573",
  },
  {
    date: "08/21/2026",
    time: "02:35 PM",
    visitorName: "Elaine Bautista",
    visitorID: "2759031846",
  },
  {
    date: "08/20/2026",
    time: "10:10 AM",
    visitorName: "Francis Navarro",
    visitorID: "8174620953",
  },
  {
    date: "08/19/2026",
    time: "03:05 PM",
    visitorName: "Beatrice Lim",
    visitorID: "4069281753",
  },
];

function displayVisitorHistory() {
  const tableBody = document.getElementById("visitorTable");
  const pagination = document.getElementById("paginationContainer");

  const visitorPages = [];

  for (let i = 0; i < visitors.length; i += 10) {
    visitorPages.push(visitors.slice(i, i + 10));
  }

  function displayPage(pageIndex) {
    tableBody.innerHTML = visitorPages[pageIndex]
      .map(
        (visitor, index) => `
        <tr>
          <td>${pageIndex * 10 + index + 1}</td>
          <td>${visitor.date}</td>
          <td>${visitor.time}</td>
          <td>${visitor.visitorName}</td>
          <td>${visitor.visitorID}</td>
        </tr>
      `,
      )
      .join("");

    document.querySelectorAll(".pageButton").forEach((button, index) => {
      button.classList.toggle("active", index === pageIndex);
    });
  }

  visitorPages.forEach((page, index) => {
    const button = document.createElement("button");

    button.className = "pageButton";
    button.textContent = index + 1;
    button.onclick = function () {
      displayPage(index);
    };

    pagination.appendChild(button);
  });

  displayPage(0);
}
